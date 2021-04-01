<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Carbon;

class PurchaseRequest extends AbstractRequest
{

    public function setIpAddress($value)
    {
        return $this->setParameter('ip_adress', $value);
    }

    public function getIpAddress()
    {
        return $this->getParameter('ip_adress');
    }

    public function getItems()
    {
        $items = [];

        if (isset($this->getParameter('additional_info')['items'])) {
            $items = $this->getParameter('additional_info')['items'];
        }

        return $items;
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
        $items = $this->getItemData();

        $dateOfExpiration = $this->getDateOfExpiration();

        $paymentMethod = $this->getCardBand();

        if ($this->getPaymentMethod() == 'boleto') {
            $paymentMethod = 'bolbradesco';
            $dateOfExpiration = Carbon::parse($dateOfExpiration)->format('Y-m-d'). 'T12:00:00.000-0300';
        }

        $purchase = [
            'additional_info' => [
                'items' => $items,
                'ip_address' => $this->getIpAddress()
            ],
            'date_of_expiration' => $dateOfExpiration,
            'external_reference' => $this->getExternalReference(),
            'notification_url' => $this->getNotificationUrl(),
            'payment_method_id' => $paymentMethod,
            'statement_descriptor' => $this->getStatementDescriptor(),
            'payer' => $this->getPayer(),
            'transaction_amount' => (double) $this->getAmount()
        ];

        if ($this->getPaymentMethod() == 'credit_card') {
            $purchase['token'] = $this->getCard();
        }

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
