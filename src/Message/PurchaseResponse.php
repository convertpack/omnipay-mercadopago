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

        if ($data['payment_type_id'] == 'ticket') {
            $dateOfExpiration = date('Y-m-d', strtotime($data['date_of_expiration'])) . 'T22:00:00-0300';

            $boletoBarCode = Arr::get($data, 'barcode.content');
            $boletoURL = Arr::get($data, 'transaction_details.external_resource_url');
        }

        return [
            'id' => (string) $data['id'],
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
}
