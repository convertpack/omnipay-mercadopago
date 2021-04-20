<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class FindOrCreateCustomerResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $customer = $this->data;

        if ($results = Arr::get($this->data, 'data.results')) {
            $searchEmail= Arr::get($this->getRequest()->getParameters(), 'email');

            $customer = Arr::first($results, fn ($customerResult) => $customerResult['email'] === $searchEmail);
        }

        return ! is_null($customer);
    }

    public function getData()
    {
        return $this->data;
    }
}
