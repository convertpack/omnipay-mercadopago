<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

class FindOrCreateCustomerRequest extends BaseAbstractRequest
{
    protected $liveEndpoint = 'https://api.mercadopago.com/v1/';
    protected $testEndpoint = 'https://api.mercadopago.com/v1/';
    
    public function sendData($data) 
    {
        $dataToFind['email'] = Arr::get($data, 'payer.email');

        $url = $this->getEndpoint().'customers/search?access_token=' . $this->getAccessToken();
        $httpRequest = $this->httpClient->request(
            'GET',
            $url,
            [
                'Content-type' => 'application/json',
                'Accept' => 'application/json'
            ],
            $this->toJSON($dataToFind)
        );
        
        $content = json_decode($httpRequest->getBody()->getContents(), true);
        
        $isSuccess = true;
        
        if (!in_array($httpRequest->getStatusCode(), [201, 200])) {
            $url = $this->getEndpoint().'customers?access_token=' . $this->getAccessToken();
            $httpRequest = $this->httpClient->request(
                'POST',
                $url,
                [
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                $this->toJSON($data)
            );
            
            $content = json_decode($httpRequest->getBody()->getContents(), true);
            
            $isSuccess = true;

            if (!in_array($httpRequest->getStatusCode(), [201, 200])) {
                $isSuccess = false;
            }
        } else {
            $content = Arr::get($content, 'results', []);
            $content = Arr::first($content, fn ($result) => $result['email'] === $dataToFind['email']);
        }
        
        $data = [
            'data' => $content,
            'status_code' => $httpRequest->getStatusCode(),
            'is_success' => $isSuccess
        ];
        
        return $this->createResponse($data);
    }

    public function getData()
    {
        return $this->getParameters();
    }
    
    /*
    * Mercado Pago access token (private key)
    */
    public function setAccessToken($value)
    {
        return $this->setParameter('access_token', $value);
    }
    
    public function getAccessToken()
    {
        return $this->getParameter('access_token');
    }
    
    /*
    * General functions
    */
    public function toJSON($data, $options = 0)
    {
        return json_encode($data, $options | 64);
    }
    
    protected function createResponse($req)
    {
        return $this->response = new FindOrCreateCustomerResponse($this, $req);
    }
    
    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/customers/search') : ($this->liveEndpoint . '/customers/search');
    }
    
}
