<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class FindCustomerResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return $this->data['is_success'];
    }

    public function getData()
    {
        $customers = (array) Arr::get($this->data, 'data.results');

        if (count($customers) == 0) {
            return null;
        }

        $searchEmail= Arr::get($this->getRequest()->getParameters(), 'email');

        $customer = Arr::first($customers, fn ($customerResult) => $customerResult['email'] === $searchEmail);

        return $customer;
    }
}
