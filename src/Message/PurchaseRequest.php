<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Omnipay\MercadoPago\Gateway;

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
        $append = [];
        $paymentMethod = $this->getPaymentMethod();
        $dateOfExpiration = $this->getDateOfExpiration();

        // Mercado Pago has a strange way of dealing with payment method.
        // Instead of just `credit_card` or `boleto` - like every other gateway,
        // they want to know EXACTLY who will be processing this charge,
        // like VISA, MASTER or AMEX
        $paymentMethodId = null;
        $issuerId = null;

        $payer = $this->getPayerFormatted();
        $append['payer'] = $payer;

        $gateway = new Gateway();
        $gateway->setAccessToken($this->getAccessToken());

        $responseCreateCustomer = $gateway->findOrCreateCustomer(['payer' => $append['payer']])->send();

        if ($responseCreateCustomer->isSuccessful() && Arr::get($responseCreateCustomer->getData(), 'data.id')) {
            $payer = Arr::get($responseCreateCustomer->getData(), 'data');
            $append['payer'] = ['id' => Arr::get($responseCreateCustomer->getData(), 'data.id')];
            $this->setPayer($append['payer']);
        }

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
            $append['token'] = $card['token'];

            if (Arr::get($payer, 'id')) {
                $responseCreateCard = $gateway->createCard(['card_token' => $append['token'], 'payer' => $payer])->send();

                if ($responseCreateCard->isSuccessful() && $cardId = Arr::get($responseCreateCard->getData(), 'data.id')) {
                    $append['card'] = ['id' => $cardId];
                    unset($append['token']);
                    $this->setCardId($cardId);
                }
            }

            $issuerId = Arr::get($card, 'issuer_id');
        }

        $data = [
            'payment_method_id' => $paymentMethodId,
            'issuer_id' => $issuerId,
            'transaction_amount' => (double) $this->getAmount(),
            'installments' => (int) $this->getInstallments(),
            'date_of_expiration' => $dateOfExpiration,
            'notification_url' => $this->getNotifyUrl(),
            'statement_descriptor' => $this->getStatementDescriptor(),
            'external_reference' => $this->getTransactionId(),
            'additional_info' => [
                'items' => $this->getItems(),
                'ip_address' => $this->getClientIp(),
            ],
            'binary_mode' => self::BINARY_MODE,
        ];

        $data = Arr::collapse([
            $append, $data
        ]);

        return $data;
    }

    public function setCardId($value)
    {
        return $this->setParameter('card_id', $value);
    }

    public function getCardId()
    {
        return $this->getParameter('card_id');
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
