<?php

namespace Omnipay\MercadoPago\Message;

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
    public function getMapped(): array
    {
        $content = $this->getData();

        $data = $content['data'];

        $dateOfExpiration = null;

        $boletoBarCode = null;
        $boletoURL = null;

        if ($data['payment_type_id'] == 'ticket') {
            $dateOfExpiration = date('Y-m-d', strtotime($data['date_of_expiration'])) . 'T22:00:00-0300';

            $boletoBarCode = $data['barcode']['content'];
            $boletoURL = $data['transaction_details']['external_resource_url'];
        }

        return [
            'provider_id' => (string)$data['id'],
            'order.installments' => (int)$data['installments'],
            'order.installments_fee' => $this->getFeeByKey('financing_fee'), // only MP - installments fee
            'order.gateway_fee' => $this->getFeeByKey('mercadopago_fee'), // only MP - processing fee
            'payment.detail' => $data['status_detail'],
            'payment.boleto_barcode' => onlyNumbers(convertITFFebraban($boletoBarCode)),
            'payment.boleto_url' => $boletoURL,
            'payment.date_of_expiration' => $dateOfExpiration,
            'gateway.fee' => $this->getFee($data),
        ];
    }

    /**
     *
     *
     * @var String $key
     * @return mixed
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
     *
     *
     * @return float
     */
    public function getFee(): float
    {
        $content = $this->getData();

        $transaction_amount = (float) isset($content['transaction_amount']) ? $content['transaction_amount'] : 0;
        $net_received_amount = (float) isset($content['transaction_details']) && isset($content['transaction_details']['net_received_amount']) ? $content['transaction_details']['net_received_amount'] : 0;

        if($net_received_amount > 0) {
            $fee = ($transaction_amount - $net_received_amount) * 100;

            return (int)$fee;
        }

        return 0;
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
}
