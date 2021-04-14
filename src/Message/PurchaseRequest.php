<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Carbon;

class PurchaseRequest extends AbstractRequest
{
    // Constants in case the incoming
    // data comes in different names
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'boleto';
    const PIX = 'pix';

    // Settings
    const BINARY_MODE = true;

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

        /*
         * Boleto
         */
        if ($paymentMethod === self::BOLETO) {
            $paymentMethodId = 'bolbradesco';

            // We expected a ISO 8601 datetime like `2025-12-01T10:15:50-03:00`
            // But Mercado Pago expects a datetime with milliseconds.
            // Example: `2025-12-01T10:15:50.0000-03:00`
            // So we must transform it and manually change the
            // time to the end of the day.
            $datetime = substr($dateOfExpiration, 0, 10); // 2025-12-01
            $dateOfExpiration = $datetime . 'T22:00:00.000-0300';
        }
        /*
         * Pix
         */
        else if ($paymentMethod === self::PIX) {
            $paymentMethodId = 'pix';

            // We expected a ISO 8601 datetime like `2025-12-01T10:15:50-03:00`
            // But Mercado Pago expects a datetime with milliseconds.
            // Example: `2025-12-01T10:15:50.0000-03:00`
            // So we must transform it...
            $datetime = substr($dateOfExpiration, 0, 19); // 2025-12-01T10:15:50
            $timezone = substr($dateOfExpiration, -6); // -03:00
            $dateOfExpiration = $datetime . '.000' . $timezone;
        }
        /*
         * Credit card
         */
        else if ($paymentMethod === self::CREDIT_CARD) {
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
            'payer' => $this->getPayerFormatted(),
            'notification_url' => $this->getNotifyUrl(),
            'statement_descriptor' => $this->getStatementDescriptor(),
            'external_reference' => $this->getTransactionId(),
            'additional_info' => [
                'items' => $this->getItems(),
                'ip_address' => $this->getIpAddress(),
            ],
            'binary_mode' => self::BINARY_MODE,
        ];
    }

    public function getHttpMethod(): string
    {
        return 'POST';
    }

    protected function createResponse($req)
    {
        return $this->response = new PurchaseResponse($this, $req);
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/payments') : ($this->liveEndpoint . '/payments');
    }

}
