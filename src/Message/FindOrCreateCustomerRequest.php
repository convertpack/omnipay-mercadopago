<?php

namespace Omnipay\MercadoPago\Message;

use Omnipay\MercadoPago\Gateway;
use Omnipay\MercadoPago\Message\AbstractRequest;

class FindOrCreateCustomerRequest extends AbstractRequest
{
    public function __invoke()
    {
        $gateway = new Gateway();
        $gateway->setAccessToken($this->getAccessToken());
        $payer = $this->getPayerFormatted();

        $responseFind = $gateway->findCustomer($payer)->send();

        if ($responseFind->isSuccessful()) {
            return $this->createResponse($responseFind->getData());
        }

        $responseCreate = $gateway->createCustomer($payer)->send();

        return $this->createResponse($responseCreate->getData());
    }

    protected function createResponse($req)
    {
        return $this->response = new FindOrCreateCustomerResponse($this, $req);
    }

}
