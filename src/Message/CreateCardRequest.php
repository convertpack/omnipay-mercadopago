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
            'payer' => Arr::get($this->getPayer(), 'id')
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
        $payerId = Arr::get($this->getPayer(), 'id');
        return $this->getTestMode() ? ($this->testEndpoint . "/customers/{$payerId}/cards") : ($this->liveEndpoint . "/customers/{$payerId}/cards");
    }

}
