<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class CreateCardRequest extends AbstractRequest
{
    public function setCardToken($value)
    {
        return $this->setParameter('card_token', $value);
    }

    public function getCardToken()
    {
        return $this->getParameter('card_token');
    }

    public function getData()
    {
        return [
            'token' => $this->getCardToken(),
            'payer' => Arr::get($this->getCustomer(), 'id')
        ];
    }

    public function getHttpMethod(): string
    {
        return 'POST';
    }

    protected function createResponse($req)
    {
        return $this->response = new CreateCardResponse($this, $req);
    }

    protected function getEndpoint()
    {
        $customerId = Arr::get($this->getCustomer(), 'id');
        return $this->getTestMode() ? ($this->testEndpoint . "/customers/{$customerId}/cards") : ($this->liveEndpoint . "/customers/{$customerId}/cards");
    }

}
