<?php

namespace Omnipay\MercadoPago;

use Omnipay\Common\AbstractGateway;
use Omnipay\MercadoPago\Message\ValidateIntegrationRequest;
use Omnipay\MercadoPago\Message\TokenRequest;
use Omnipay\MercadoPago\Message\PurchaseRequest;
use Omnipay\MercadoPago\Message\CompletePurchaseRequest;

class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'MercadoPago';
    }

    public function setConfig($value)
    {
        $this->setParameter('access_token', isset($value['access_token']) ? $value['access_token'] : null);
        $this->setParameter('client_secret', isset($value['client_secret']) ? $value['client_secret'] : null);
        return $this;
    }

    public function getConfig()
    {
        return [
            'access_token' => $this->getParameter('access_token'),
            'client_secret' => $this->getParameter('client_secret')
        ];
    }

    public function setAccessToken($value)
    {
        return $this->setParameter('access_token', $value);
    }

    public function getAccessToken()
    {
        return $this->getParameter('access_token');
    }

    public function setNotificationUrl($value)
    {
        return $this->setParameter('notification_url', $value);
    }

    public function getNotificationUrl()
    {
        return $this->getParameter('notification_url');
    }

    public function setExternalReference($value)
    {
        return $this->setParameter('external_reference', $value);
    }

    public function getExternalReference()
    {
        return $this->getParameter('external_reference');
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\TokenRequest
     */
    public function requestToken(array $parameters = [])
    {
        return $this->createRequest(TokenRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\ValidateIntegrationRequest
     */
    public function validateIntegration(array $parameters = [])
    {
        return $this->createRequest(ValidateIntegrationRequest::class, $parameters);
    }

}
