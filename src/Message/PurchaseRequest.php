<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Carbon;

class PurchaseRequest extends AbstractRequest
{
    // Constants in case the incoming
    // data comes in different names
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'boleto';

    // Settings
    const BINARY_MODE = true;
    const CAMPAIGN_ID = 'convertpack';

    public function getData()
    {
        $paymentMethod = $this->getPaymentMethod();
        $dateOfExpiration = $this->getDateOfExpiration();
        
        // Mercado Pago has a strange way of dealing with payment method.
        // Instead of just `credit_card` or `boleto` - like every other gateway,
        // they want to know EXACTLY who will be processing this charge,
        // like VISA, MASTER or AMEX
        $paymentMethodId = null;
        $cardToken = null;
        $issuerId = null;

        // Boleto
        if ($paymentMethod === self::BOLETO) {
            $paymentMethodId = 'bolbradesco';

            // We expected `2025-12-01` from incoming request
            // and manually add the time (end of the day)
            // Be careful with format: time must be `HH:MM:SS.000`
            $dateOfExpiration = $dateOfExpiration . 'T22:00:00.000-0300';
        }
        // Credit card
        else if ($paymentMethod == self::CREDIT_CARD) {
            $card = $this->getCard();
            $paymentMethodId = $card['payment_method_id'];
            $cardToken = $card['token'];
            $issuerId = $card['issuer_id'];
        }

        return [
            'payment_method_id' => $paymentMethodId,
            'issuer_id' => $issuerId,
            'token' => $cardToken,
            'transaction_amount' => (double) $this->getAmount(),
            'installments' => (int) $this->getInstallments(),
            'date_of_expiration' => $dateOfExpiration,
            'payer' => $this->getPayer(),
            'notification_url' => $this->getNotifyUrl(),
            'statement_descriptor' => $this->getStatementDescriptor(),
            'external_reference' => $this->getTransactionId(),
            'additional_info' => [
                'items' => $this->getItems(),
                'ip_address' => $this->getIpAddress()
            ],
            'binary_mode' => self::BINARY_MODE,
            'campaign_id' => self::CAMPAIGN_ID,
        ];
    }

    protected function createResponse($req)
    {
        return $this->response = new PurchaseResponse($this, $req);
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/checkout/preferences') : ($this->liveEndpoint . '/checkout/preferences');
    }

}
