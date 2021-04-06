<?php

namespace Omnipay\MercadoPago\Message;

class FetchPurchaseRequest extends AbstractRequest
{

    public function getData()
    {
        $this->validate('transactionReference');

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
        return $this->getTestMode() ? ($this->testEndpoint . 'payments/' . $this->getData()) : ($this->liveEndpoint . 'payments/' . $this->getData());
    }

}
