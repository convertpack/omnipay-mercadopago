<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class FindCardRequest extends AbstractRequest
{

    public function getData()
    {
        return [
            'id' => Arr::get($this->getCard(), 'id'),
        ];
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function createResponse($req)
    {
        return $this->response = new FindCardResponse($this, $req);
    }

    protected function getEndpoint()
    {
        $payerId = Arr::get($this->getPayer(), 'id');
        $cardId = Arr::get($this->getCard(), 'id');
        return $this->getTestMode() ? ($this->testEndpoint . "/customers/{$payerId}/cards/{$cardId}") : ($this->liveEndpoint . "/customers/{$payerId}/cards/{$cardId}");
    }

}
