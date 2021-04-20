<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Omnipay\Common\Message\AbstractResponse as BaseAbstractResponse;

class AbstractResponse extends BaseAbstractResponse
{
    public function isSuccessful()
    {
        return $this->data['is_success'];
    }

    /**
     * Returns error message
     *
     * @return int
     */
    public function getMessage(): string
    {
        $message = Arr::get($this->data, 'data.message', '');

        return $message;
    }

    /**
     * Returns status code request
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int) Arr::get($this->data, 'status_code', 0);
    }
}
