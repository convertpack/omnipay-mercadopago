<?php

namespace Omnipay\MercadoPago\Message;

use Omnipay\Common\Message\AbstractRequest as MessageAbstractRequest;
use Omnipay\MercadoPago\Item;

abstract class AbstractRequest extends MessageAbstractRequest
{
    protected $liveEndpoint = 'https://api.mercadopago.com/v1/';
    protected $testEndpoint = 'https://api.mercadopago.com/v1/';

    public function sendData($data)
    {
        $url = $this->liveEndpoint . 'payments?access_token=' . $this->getAccessToken();
        $httpRequest = $this->httpClient->request(
            'POST',
            $url,
            [
                'Content-type' => 'application/json',
                'Accept' => 'application/json'
            ],
            $this->toJSON($data)
        );

        $content = json_decode($httpRequest->getBody()->getContents(), true);

        $isSuccess = true;

        if ($httpRequest->getStatusCode() != 201 || $httpRequest->getStatusCode() != 200) {
            $isSuccess = false;
        }

        return $this->createResponse([
            'data' => $content,
            'status_code' => $httpRequest->getStatusCode(),
            'is_success' => $isSuccess
        ]);
    }

    public function setExternalReference($value)
    {
        return $this->setParameter('external_reference', $value);
    }

    public function getExternalReference()
    {
        return $this->getParameter('external_reference');
    }

    public function setStatementDescriptor($value)
    {
        return $this->setParameter('statement_descriptor', $value);
    }

    public function getStatementDescriptor()
    {
        return $this->getParameter('statement_descriptor');
    }

    public function setDateOfExpiration($value)
    {
        return $this->setParameter('date_of_expiration', $value);
    }

    public function getDateOfExpiration()
    {
        return $this->getParameter('date_of_expiration');
    }

    public function setPaymentMethodId($value)
    {
        return $this->setParameter('payment_method_id', $value);
    }

    public function getPaymentMethodId()
    {
        return $this->getParameter('payment_method_id');
    }

    public function setAdditionalInfo($value)
    {
        return $this->setParameter('additional_info', $value);
    }

    public function getAdditionalInfo()
    {
        return $this->getParameter('additional_info');
    }

    public function setAccessToken($value)
    {
        return $this->setParameter('access_token', $value);
    }

    public function getAccessToken()
    {
        return $this->getParameter('access_token');
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    public function setNotificationUrl($value)
    {
        return $this->setParameter('notification_url', $value);
    }

    public function getNotificationUrl()
    {
        return $this->getParameter('notification_url');
    }

    public function setPayer($value)
    {
        return $this->setParameter('payer', $value);
    }

    public function getPayer()
    {
        return $this->getParameter('payer');
    }

    public function toJSON($data, $options = 0)
    {
        return json_encode($data, $options | 64);
    }

}
