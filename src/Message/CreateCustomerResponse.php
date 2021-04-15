<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class CreateCustomerResponse extends AbstractResponse
{
    public function getId()
    {
        return Arr::get($this->data, 'data.id');
    }
}
