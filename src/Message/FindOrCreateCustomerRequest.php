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

        if (count(Arr::get($content, 'results', [])) == 0) {
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

    public function getData()
    {
        return [
            'payer' => $this->getPayerFormatted()
        ];
    }

    public function setPayer($value)
    {
        return $this->setParameter('payer', $value);
    }

    public function getPayer()
    {
        return $this->getParameter('payer');
    }

    public function getPayerFormatted()
    {
        $payer = $this->getPayer();

        $data = [
            "email" => Arr::get($payer, 'email', ''),
            "first_name" => Arr::get($payer, 'first_name', ''),
            "last_name" => Arr::get($payer, 'last_name', '')
        ];

        // if ($phone = Arr::get($payer, 'phone')) {
        //     $data["phone"] = [
        //         "area_code" => Arr::get($phone, 'ddi', ''),
        //         "number" => Arr::get($phone, 'number', '')
        //     ];
        // }

        if ($document = Arr::get($payer, 'document')) {
            $data["identification"] = $document;
        }

        if ($address = Arr::get($payer, 'address')) {
            $data['address'] = [
                "zip_code" => Arr::get($address, 'zip_code'),
                "street_name" => Arr::get($address, 'street_name'),
                "street_number" => Arr::get($address, 'street_number'),
            ];
        }

        return $data;
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
        return $this->getTestMode() ? ($this->testEndpoint) : ($this->liveEndpoint);
    }

}
