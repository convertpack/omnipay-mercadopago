<?php

namespace Omnipay\MercadoPago\Message;

use Omnipay\MercadoPago\Gateway;

class FindOrCreateCustomerRequest extends AbstractRequest
{
    protected $response;

    public function run()
    {
        $gateway = new Gateway;
        $gateway->setAccessToken($this->getAccessToken());
        $payer = $this->getPayerFormatted();

        $responseFind = $gateway->findCustomer(['email' => $payer['email']])->send();

        if ($responseFind->isSuccessful() && $responseFind->getData()) {
            $this->response = $this->createResponse($responseFind->getData());
        } else {
            $responseCreate = $gateway->createCustomer(['payer' => $payer])->send();

            $this->response = $this->createResponse($responseCreate->getData());
        }

        return $this->response;
    }

    public function getData()
    {
        return $this->response;
    }

    public function getHttpMethod(): string
    {
        return 'GET';
    }

    protected function createResponse($req)
    {
        return $this->response = new FindOrCreateCustomerResponse($this, $req);
    }

}
