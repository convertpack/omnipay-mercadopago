<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Carbon;

class PurchaseRequest extends AbstractRequest
{
    // Constants in case the incoming
    // data comes in different names
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'boleto';

    public function setIpAddress($value)
    {
        return $this->setParameter('ip_address', $value);
    }

    public function getIpAddress()
    {
        return $this->getParameter('ip_address');
    }

    public function getItemData()
    {
        $data = [];
        $items = $this->getItems();

        if ($items) {
            foreach ($items as $n => $item) {

                $item_array['id'] = $item['id'];
                $item_array['title'] = $item['title'];
                $item_array['description'] = isset($item['description']) ? $item['description'] : '';
                $item_array['picture_url'] = $item['picture_url'];
                $item_array['quantity'] = (int) $item['quantity'];
                $item_array['unit_price'] = (double)($this->formatCurrency($item['unit_price']));

                array_push($data, $item_array);
            }
        }

        return $data;
    }

    public function getData()
    {
        $paymentMethod = $this->getPaymentMethod();
        $items = $this->getItemData();
        $dateOfExpiration = $this->getDateOfExpiration();
        
        // Mercado Pago has a strange way of dealing with payment method.
        // Instead of just `credit_card` or `boleto` - like every other gateway,
        // they want to know EXACTLY who will be processing this charge,
        // like VISA, MASTER or AMEX
        $paymentMethodId = null;

        if ($paymentMethod === self::BOLETO) {
            $paymentMethodId = 'bolbradesco';
            $dateOfExpiration = Carbon::parse($dateOfExpiration)->format('Y-m-d'). 'T21:00:00.000-0300';
        } else if ($paymentMethod == self::CREDIT_CARD) {
            $card = $this->getCard();

            $paymentMethodId = $card['payment_method_id'];
            $purchase['token'] = $card['token'];
            $purchase['issuer_id'] = $card['issuer_id'];
        }

        $purchase = [
            'payment_method_id' => $paymentMethodId,
            'transaction_amount' => (double) $this->getAmount(),
            'installments' => (int) $this->getInstallments(),
            'date_of_expiration' => $dateOfExpiration,
            'payer' => $this->getPayer(),
            'notification_url' => $this->getNotificationUrl(),
            'statement_descriptor' => $this->getStatementDescriptor(),
            'external_reference' => $this->getExternalReference(),
            'additional_info' => [
                'items' => $items,
                'ip_address' => $this->getIpAddress()
            ],
        ];

        return $purchase;
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
