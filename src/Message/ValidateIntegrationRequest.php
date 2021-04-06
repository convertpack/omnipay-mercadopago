<?php

namespace Omnipay\MercadoPago\Message;

class ValidateIntegrationRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://api.mercadopago.com/v1/';
    /** @var this option is unavailable */
    protected $testEndpoint = 'https://api.mercadopago.com/v1/';


    public function sendData($data)
    {
        $url = $this->getEndpoint() . "identification_types?access_token=" . $this->getAccessToken();

        $httpRequest = $this->httpClient->request(
            'GET',
            $url,
            [
                'Content-type' => 'application/json',
                'Accept' => 'application/json'
            ]
        );

        $content = json_decode($httpRequest->getBody()->getContents());

        $isSuccess = true;

        if (!in_array($httpRequest->getStatusCode(), [201, 200])) {
            $isSuccess = false;
        }

        return $this->createResponse([
            'data' => $content,
            'status_code' => $httpRequest->getStatusCode(),
            'is_success' => $isSuccess
        ]);
    }

    public function getData()
    {
        return [];
    }

    protected function createResponse($data)
    {
        return $this->response = new ValidateIntegrationResponse($this, $data);
    }

}
