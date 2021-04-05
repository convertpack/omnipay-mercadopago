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

        $dateOfExpiration = null;

        $boletoBarCode = null;
        $boletoURL = null;

        // If payment method is 'Boleto'
        if ($data['payment_type_id'] == 'ticket') {
            $dateOfExpiration = date('Y-m-d', strtotime($data['date_of_expiration'])) . 'T22:00:00-0300';

            $rawBarCode = Arr::get($data, 'barcode.content'); 
            $boletoBarCode = $this->convertItfBoleto($rawBarCode);

            $boletoURL = Arr::get($data, 'transaction_details.external_resource_url');
        }

        return [
            'provider_id' => (string) $data['id'],
            'installments' => (int) $data['installments'],
            'installments_fee' => $this->getFeeByKey('financing_fee'), // only MP - installments fee
            'gateway_fee' => $this->getFeeByKey('mercadopago_fee'), // only MP - processing fee
            'detail' => $data['status_detail'],
            'boleto_barcode' => $boletoBarCode,
            'boleto_url' => $boletoURL,
            'date_of_expiration' => $dateOfExpiration,
            'fee' => $this->getFee($data),
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
    public function getFeeByKey(string $key): int
    {
        $content = $this->getData();

        $fee = array_first($content['data']['fee_details'], fn ($item) => $item['type'] == $key);

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
    public function getFee(): int
    {
        $content = $this->getData();

        $transaction_amount = (float) isset($content['transaction_amount']) ? $content['transaction_amount'] : 0;
        $net_received_amount = (float) isset($content['transaction_details']) && isset($content['transaction_details']['net_received_amount']) ? $content['transaction_details']['net_received_amount'] : 0;

        if($net_received_amount > 0) {
            $fee = ($transaction_amount - $net_received_amount) * 100;

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
