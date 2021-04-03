<?php

namespace Omnipay\MercadoPago;

use Omnipay\Common\AbstractGateway;
use Omnipay\MercadoPago\Message\TokenRequest;
use Omnipay\MercadoPago\Message\PurchaseRequest;
use Omnipay\MercadoPago\Message\CompletePurchaseRequest;

class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'MercadoPago';
    }

    public function getClientId()
    {
        return $this->getParameter('client_id');
    }

    public function setClientId($value)
    {
        return $this->setParameter('client_id', $value);
    }

    public function getClientSecret()
    {
        return $this->getParameter('client_secret');
    }

    public function setClientSecret($value)
    {
        return $this->setParameter('client_secret', $value);
    }

    public function getGrantType()
    {
        return $this->getParameter('grant_type');
    }

    public function setGrantType($value)
    {
        return $this->setParameter('grant_type', $value);
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

    // public function parse($parameters)
    // {
    //     $paymentMethod = $this->getPaymentMethod();
    //     $items = $this->getItemData();
    //     $dateOfExpiration = $this->getDateOfExpiration();
        
    //     // Mercado Pago has a strange way of dealing with payment method.
    //     // Instead of just `credit_card` or `boleto` - like every other gateway,
    //     // they want to know EXACTLY who will be processing this charge,
    //     // like VISA, MASTER or AMEX
    //     $paymentMethodId = null;
    //     $cardToken = null;
    //     $issuerId = null;

    //     if ($paymentMethod === 'boleto') {
    //         $paymentMethodId = 'bolbradesco';

    //         // We expected `2025-12-01` from incoming request
    //         // and manually add the time (end of the day)
    //         // Be careful with format: time must be `HH:MM:SS.000`
    //         $dateOfExpiration = $dateOfExpiration . 'T22:00:00.000-0300';
    //     } else if ($paymentMethod == 'credit_card') {
    //         $card = $this->getCard();
    //         $paymentMethodId = $card['payment_method_id'];
    //         $cardToken = $card['token'];
    //         $issuerId = $card['issuer_id'];
    //     }

    //     $parameters = [
    //         'payment_method_id' => $paymentMethodId,
    //         'issuer_id' => $issuerId,
    //         'token' => $cardToken,
    //         'transaction_amount' => (double) $this->getAmount(),
    //         'installments' => (int) $this->getInstallments(),
    //         'date_of_expiration' => $dateOfExpiration,
    //         'payer' => $this->getPayer(),
    //         'notification_url' => $this->getNotificationUrl(),
    //         'statement_descriptor' => $this->getStatementDescriptor(),
    //         'external_reference' => $this->getStoreTransactionId(),
    //         'additional_info' => [
    //             'items' => $items,
    //             'ip_address' => null, //$this->getIpAddress()
    //         ],
    //         'binary_mode' => true,
    //         'campaign_id' => 'convertpack',
    //     ];

    //     return $parameters;
    // }

    /**
     * @param  array  $parameters
     * @return \Omnipay\MercadoPago\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        // $parsed = $this->parse($parameters);
        
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

}
