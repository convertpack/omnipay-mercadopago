<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;

class FetchPurchaseResponse extends AbstractResponse
{
    public function getTransactionStatus()
    {
        return Arr::get($this->data, 'status');
    }

    public function getTransactionStatusDetail()
    {
        return Arr::get($this->data, 'status_detail');
    }
}
