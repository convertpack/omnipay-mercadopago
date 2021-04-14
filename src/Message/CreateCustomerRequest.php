<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class CreateCustomerRequest extends AbstractRequest
{
    public function getData()
    {
        return $this->getPayerFormatted();
    }
    
    public function getHttpMethod(): string
    {
        return 'POST';
    }
    
    protected function createResponse($req)
    {
        return $this->response = new CreateCustomerResponse($this, $req);
    }
    
    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/customers') : ($this->liveEndpoint . '/customers');
    }
    
}
