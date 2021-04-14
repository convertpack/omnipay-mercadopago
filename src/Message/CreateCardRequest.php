<?php

namespace Omnipay\MercadoPago\Message;

class CreateCardRequest extends AbstractRequest
{
    public function setCardToken($value)
    {
        return $this->setParameter('card_token', $value);
    }

    public function getCardToken()
    {
        return $this->getParameter('card_token');
    }

    public function setPayerId($value)
    {
        return $this->setParameter('payer_id', $value);
    }

    public function getPayerId()
    {
        return $this->getParameter('payer_id');
    }

    public function getData()
    {
        return [
            'token' => $this->getCardToken()
        ];
    }
    
    public function getHttpMethod(): string
    {
        return 'POST';
    }
    
    protected function createResponse($req)
    {
        return $this->response = new CreateCardResponse($this, $req);
    }
    
    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . "/customers/{$this->getPayerId()}/cards") : ($this->liveEndpoint . "/customers/{$this->getPayerId()}/cards");
    }
    
}
