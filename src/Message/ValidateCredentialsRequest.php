<?php

namespace Omnipay\MercadoPago\Message;

class ValidateCredentialsRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://api.mercadopago.com/v1/';
    protected $testEndpoint = 'https://api.mercadopago.com/v1/';

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    public function getData()
    {
        return [];
    }

    protected function createResponse($data)
    {
        return $this->response = new ValidateCredentialsResponse($this, $data);
    }

    /*
     * We use `identification_types` route because
     * it needs authentication, but doesn't return
     * any sensitive information
     */
    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/identification_types') : ($this->liveEndpoint . '/identification_types');
    }

}
