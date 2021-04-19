<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class FindCustomerResponse extends AbstractResponse
{
    public function getData()
    {
        $customers = Arr::get($this->data, 'results');

        $searchEmail= Arr::get($this->getRequest()->getParameters(), 'email');

        $customer = Arr::first($customers, fn ($customerResult) => $customerResult === $searchEmail);

        return $customer;
    }
}
