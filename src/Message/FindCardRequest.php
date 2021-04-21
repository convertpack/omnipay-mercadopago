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
        $customerId = Arr::get($this->getCustomer(), 'id');
        $cardId = Arr::get($this->getCard(), 'id');
        return $this->getTestMode() ? ($this->testEndpoint . "/customers/{$customerId}/cards/{$cardId}") : ($this->liveEndpoint . "/customers/{$customerId}/cards/{$cardId}");
    }

}
