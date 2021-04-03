<?php

namespace Omnipay\MercadoPago\Message;

use Omnipay\Common\Message\AbstractRequest as MessageAbstractRequest;

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

        if (!in_array($httpRequest->getStatusCode(), [201, 200])) {
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

    public function setItems($itemsArray)
    {
        return $this->setParameter('items', $itemsArray);
    }

    public function getItems()
    {
        return $this->getParameter('items');
    }

    public function setInstallments($value)
    {
        return $this->setParameter('installments', $value);
    }

    public function getInstallments()
    {
        return $this->getParameter('installments');
    }

    public function setCard($value)
    {
        return $this->setParameter('card', $value);
    }

    public function getCard()
    {
        return $this->getParameter('card');
    }

    public function setDescription($value)
    {
        return $this->setParameter('description', $value);
    }

    public function getDescription()
    {
        return $this->getParameter('description');
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
        // https://www.mercadopago.com.br/developers/pt/reference/payments/_payments/post
        $payer = $this->getParameter('payer');

        return [
            // 'entity_type' => null,
            // 'type' => null,
            // 'id' => null,
            'first_name' => $payer['first_name'],
            'last_name' => $payer['last_name'],
            'email' => $payer['email'],
            'identification' => [
                'type' => $payer['document']['type'],
                'number' => $payer['document']['number'],
            ],
            'phone' => [
                'area_code' => $payer['phone']['ddi'],
                'number' => $payer['phone']['number'],
            ],
        ];
    }

    public function toJSON($data, $options = 0)
    {
        return json_encode($data, $options | 64);
    }

}
