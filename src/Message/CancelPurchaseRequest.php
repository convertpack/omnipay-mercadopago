<?php

namespace Omnipay\MercadoPago\Message;

class CancelPurchaseRequest extends AbstractRequest
{

    public function getData()
    {
        $this->validate('transactionReference');
 
        return ['status' => 'cancelled'];
    }

    public function getTransactionReference()
    {
        return $this->getParameter('transactionReference');
    }

    public function getHttpMethod(): string
    {
        return 'PUT';
    }

    protected function createResponse($req)
    {
        return $this->response = new CancelPurchaseResponse($this, $req);
    }

    protected function getEndpoint()
    {
        $base = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
        $route = 'payments/' . $this->getTransactionReference();
        return $base . $route;
    }

}
