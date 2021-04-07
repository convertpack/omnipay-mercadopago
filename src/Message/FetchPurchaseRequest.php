<?php

namespace Omnipay\MercadoPago\Message;

class FetchPurchaseRequest extends AbstractRequest
{

    public function getData()
    {
        $this->validate('transaction_reference');

        return $this->getTransactionReference();
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function createResponse($req)
    {
        return $this->response = new FetchPurchaseResponse($this, $req);
    }

    protected function getEndpoint()
    {
        $base = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
        $route = 'payments/' . $this->getData();
        return $base . $route;
    }

}
