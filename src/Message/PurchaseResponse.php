<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function isSuccessful()
    {
        return $this->data['is_success'];
    }

    /**
     * Redirect for the Payment URL
     * @return boolean
     */
    public function isRedirect()
    {
        return isset($this->data->init_point) && $this->data->init_point;
    }


    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     *
     * @return array
     */
    public function getResume(): array
    {
        $content = $this->getData();
        $data = $content['data'];
        

        dd($data);



        $paymentTypeId = Arr::get($data, 'payment_type_id');
        $status = Arr::get($data, 'status');
        
        $totalAmount = Arr::get($data, 'transaction_details.total_paid_amount');
        $netAmount = Arr::get($data, 'transaction_details.net_received_amount');

        $dateOfExpiration = null;
        $boletoBarcode = null;
        $boletoUrl = null;

        // Boleto
        if ($paymentTypeId == 'ticket') {
            // We standardize the expiration date to 22:00:00-0300
            $rawDateOfExpiration = Arr::get($data, 'date_of_expiration');
            $dayOfExpiration = date('Y-m-d', strtotime($rawDateOfExpiration));
            $dateOfExpiration = $dayOfExpiration . 'T22:00:00-0300';

            // We standardize boleto's barcode to the most common format
            // Originally Mercado Pago sends the ITF format
            $rawBarcode = Arr::get($data, 'barcode.content'); 
            $boletoBarcode = $this->convertItfBoleto($rawBarcode);

            $boletoUrl = Arr::get($data, 'transaction_details.external_resource_url');
        }
        // Credit card
        else if ($paymentTypeId == 'credit_card') { // FIX
            
        }

        return [
            'provider_id' => (string) $data['id'],
            'installments' => (int) Arr::get($data, 'installments'),
            'installments_fee' => $this->getSpecificFee('financing_fee'), // only MP - installments fee
            'gateway_fee' => $this->getSpecificFee('mercadopago_fee'), // only MP - processing fee
            'detail' => Arr::get($data, 'status_detail'),
            'status' => Arr::get($data, 'status'),
            'boleto_barcode' => $boletoBarcode,
            'boleto_url' => $boletoUrl,
            'date_of_expiration' => $dateOfExpiration,
            'fee' => $this->getTotalFee(),
        ];
    }

    /**
     *
     *
     * @var String $key
     * @return int
     */
    public function getMessage(): string
    {
        $message = Arr::get($this->data, 'data.message', '');

        return $message;
    }

    /**
     *
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
     * Get the fee
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

    public function getRedirectData()
    {
        return null;
    }

    public function getRedirectUrl()
    {
        if ($this->isRedirect()) {
            return $this->getRequest()->getTestMode() ? $this->data->sandbox_init_point : $this->data->init_point;
        }
    }

    /*
     * Mercado Pago returns Boleto's barcode
     * in ITF format and must be converted
     * to the most common format
     */
    function convertItfBoleto($code)
    {
        $code = preg_replace('/\D/', '', $code);

        $field1 = substr($code, 0, 4) . substr($code, 19, 1) . '.' . substr($code, 20, 4);
        $field2 = substr($code, 24, 5) . '.' . substr($code, 24 + 5, 5);
        $field3 = substr($code, 34, 5) . '.' . substr($code, 34 + 5, 5);
        $field4 = substr($code, 4, 1); // "Dígito verificador"
        $field5 = substr($code, 5, 14); // Due date + amount

        if ($field5 === 0) {
            $field5 = '000';
        }

        $newCode = $field1 . modulo10($field1) 
            . $field2 . modulo10($field2)
            . $field3 . modulo10($field3)
            . $field4
            . $field5;

        return $newCode;
    }

    function modulo10($number)
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
