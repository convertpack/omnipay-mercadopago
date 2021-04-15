<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class FindCustomerRequest extends AbstractRequest
{
    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function getData()
    {
        return ['email' => $this->getEmail()];
    }
    
    public function getHttpMethod(): string
    {
        return 'GET';
    }
    
    protected function createResponse($req)
    {
        return $this->response = new FindCustomerResponse($this, $req);
    }
    
    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/customers/search') : ($this->liveEndpoint . '/customers/search');
    }
    
}
