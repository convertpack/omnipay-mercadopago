<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class FindCustomerResponse extends AbstractResponse
{
    public function getData()
    {
        $customers = Arr::get($this->data, 'results');
        
        $customer = Arr::first($customers, fn ($customerResult) => $customerResult === Arr::get($this->getRequest()->getParameters(), 'email'));

        return $customer;
    }
}
