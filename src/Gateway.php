<?php

namespace Omnipay\MercadoPago;

use Illuminate\Support\Arr;
use Omnipay\Common\AbstractGateway;
use Omnipay\MercadoPago\Message\ValidateCredentialsRequest;
use Omnipay\MercadoPago\Message\PurchaseRequest;
use Omnipay\MercadoPago\Message\FetchPurchaseRequest;
use Omnipay\MercadoPago\Message\FindCustomerRequest;
use Omnipay\MercadoPago\Message\CancelPurchaseRequest;
use Omnipay\MercadoPago\Message\CreateCustomerRequest;
use Omnipay\MercadoPago\Message\CreateCardRequest;
use Omnipay\MercadoPago\Message\FindOrCreateCustomerRequest;

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
     * @return \Omnipay\MercadoPago\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = [])
    {
        return $this->createRequest(CreateCustomerRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\FindCustomerRequest
     */
    public function findCustomer(array $parameters = [])
    {
        return $this->createRequest(FindCustomerRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\FindOrCreateCustomerRequest
     */
    public function findOrCreateCustomer(array $parameters = [])
    {
        return $this->createRequest(FindOrCreateCustomerRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\CreateCardRequest
     */
    public function createCard(array $parameters = [])
    {
        return $this->createRequest(CreateCardRequest::class, $parameters);
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
     * @return \Omnipay\MercadoPago\Message\ValidateCredentialsRequest
     */
    public function validateCredentials(array $parameters = [])
    {
        return $this->createRequest(ValidateCredentialsRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\FetchPurchaseRequest
     */
    public function fetchPurchase(array $parameters = [])
    {
        return $this->createRequest(FetchPurchaseRequest::class, $parameters);
    }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\CancelPurchaseRequest
     */
    public function cancelPurchase(array $parameters = [])
    {
        return $this->createRequest(CancelPurchaseRequest::class, $parameters);
    }

}
