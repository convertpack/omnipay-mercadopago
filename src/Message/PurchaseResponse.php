<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class PurchaseResponse extends AbstractResponse
{
    /**
     *
     * @return array
     */
    public function getFormattedData(): array
    {
        $content = $this->getData();
        $data = $content['data'];
        $sent = $content['sent'];
        $transactionReference = (string) $data['id'];
        //$this->setTransactionReference($transactionReference);

        $totalAmount = Arr::get($data, 'transaction_details.total_paid_amount', null);
        $paymentTypeId = Arr::get($data, 'payment_type_id');
        $paymentMethodId = Arr::get($data, 'payment_method_id');
        $dateOfExpiration = Arr::get($data, 'date_of_expiration');
        $append = [];

        if ($customerId = Arr::get($sent, 'customer.id')) {
            $append['customer'] = ['id' => $customerId];
        }

        /*
         * Boleto
         */
        if ($paymentMethodId === 'bolbradesco' && $paymentTypeId == 'ticket') {
            // We standardize the expiration date to 22:00:00-0300
            $dayOfExpiration = date('Y-m-d', strtotime($dateOfExpiration));
            $dateOfExpiration = $dayOfExpiration . 'T22:00:00-0300';

            // We standardize boleto's barcode to the most common format
            // Originally Mercado Pago sends the ITF format
            $rawBarcode = Arr::get($data, 'barcode.content');
            $boletoUrl = Arr::get($data, 'transaction_details.external_resource_url');

            $append['boleto_barcode'] = $this->convertItfBoleto($rawBarcode);
            $append['boleto_url'] = $boletoUrl;
        }
        /*
         * Pix
         */
        else if ($paymentMethodId === 'pix' && $paymentTypeId === 'bank_transfer') {
            $pixData = Arr::get($data, 'point_of_interaction.transaction_data');
            $pixCode = Arr::get($pixData, 'qr_code');
            $pixQrCodeBase64 = Arr::get($pixData, 'qr_code_base64');
            $pixCollectorName = Arr::get($pixData, 'bank_info.collector.account_holder_name');

            $append['pix_code'] = $pixCode;
            $append['pix_collector_name'] = $pixCollectorName;
            //$append['pix_qr_code_base64'] = $pixQrCodeBase64;
        }
        /*
         * Credit card
         */
        else if ($paymentTypeId == 'credit_card') {
            if ($card = Arr::get($sent, 'card')) {
                $append['card'] = [
                    'id' => Arr::get($card, 'id'),
                    'num_last_four' => Arr::get($data, 'card.last_four_digits'),
                    'brand' => Arr::get($sent, 'payment_method_id')
                ];
            }
        }

        // Net amount
        $netAmount = Arr::get($data, 'transaction_details.net_received_amount', 0);

        if($netAmount > 0) {
            $append['net_amount'] = (int) ($netAmount * 100);
        }

        $base = [
            'transaction_reference' => $transactionReference,
            'status' => Arr::get($data, 'status'),
            'detail' => Arr::get($data, 'status_detail'),
            'installments' => (int) Arr::get($data, 'installments'),
            'date_of_expiration' => $dateOfExpiration,

            'fees' => [
                'installments_fees' => $this->getSpecificFee('financing_fee'),
                'gateway_fees' => $this->getSpecificFee('mercadopago_fee'),
                'total' => $this->getTotalFee(),
            ],

            'total_amount' => $totalAmount,
        ];

        $base = Arr::collapse([$base, $append]);

        return $base;
    }

    /**
     * Get a specific fee by key name
     *
     * @var String $key
     * @return int
     */
    public function getSpecificFee(string $key): int
    {
        $content = $this->getData();
        $feeArray = $content['data']['fee_details'];

        $fee = array_first($feeArray, fn ($item) => $item['type'] == $key);

        if (is_null($fee)) {
            return 0;
        }

        return $fee['amount'] * 100;
    }

    /**
     * Get the total charged fee (total amount - net amount)
     *
     * @return integer
     */
    public function getTotalFee(): int
    {
        $content = $this->getData();
        $data = $content['data'];

        $totalAmount = Arr::get($data, 'transaction_details.total_paid_amount');
        $netAmount = Arr::get($data, 'transaction_details.net_received_amount', 0);

        if($netAmount > 0) {
            $fee = ($totalAmount - $netAmount) * 100;

            return (int) $fee;
        }

        return 0;
    }

    public function getStatusTransaction()
    {
        $status = Arr::get($this->data, 'data.status');

        $paymentStatus = Config::get('omnipay.mercado_pago.payment_status', []);

        return Arr::get($paymentStatus, $status, $status);
    }

    /*
     * Mercado Pago returns Boleto's barcode
     * in ITF format and must be converted
     * to the most common format
     */
    private function convertItfBoleto($string)
    {
        $code = preg_replace('/\D/', '', $string);

        $campo1 = substr($code, 0, 4) . substr($code, 19, 1) . substr($code, 20, 4);
        $campo2 = substr($code, 24, 5) . substr($code, 24 + 5, 5);
        $campo3 = substr($code, 34, 5) . substr($code, 34 + 5, 5);
        $campo4 = substr($code, 4, 1);     // Digito verificador
        $campo5 = substr($code, 5, 14);    // Vencimento + Valor

        if ($campo5 === 0) {
            $campo5 = '000';
        }

        $code = $campo1 . $this->modulo10($campo1)
            . $campo2 . $this->modulo10($campo2)
            . $campo3 . $this->modulo10($campo3)
            . $campo4
            . $campo5;

        return $code;
    }

    private function modulo10($number)
    {
        $numero = preg_replace('/\D/', '', $number);

        $soma = 0;
        $peso = 2;

        $contador = strlen($numero) - 1;

        while ($contador >= 0) {
            $multiplicacao = (substr($numero, $contador, 1) * $peso);
            if ($multiplicacao >= 10) {
                $multiplicacao = 1 + ($multiplicacao - 10);
            }
            $soma = $soma + $multiplicacao;

            if ($peso === 2) {
                $peso = 1;
            } else {
                $peso = 2;
            }
            $contador = $contador - 1;
        }

        $digito = 10 - ($soma % 10);

        if ($digito === 10) {
            $digito = 0;
        }

        return $digito;
    }
}
