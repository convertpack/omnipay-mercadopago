<?php

namespace Omnipay\MercadoPago\Message;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Omnipay\Common\Message\AbstractRequest as MessageAbstractRequest;

abstract class AbstractRequest extends MessageAbstractRequest
{
    protected $liveEndpoint = 'https://api.mercadopago.com/v1/';
    protected $testEndpoint = 'https://api.mercadopago.com/v1/';

    public function sendData($data)
    {
        $url = $this->getEndpoint().'?access_token=' . $this->getAccessToken();
        $httpRequest = $this->httpClient->request(
            $this->getHttpMethod(),
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

        return $this->createResponse([
            'data' => $content,
            'status_code' => $httpRequest->getStatusCode(),
            'is_success' => $isSuccess
        ]);
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    abstract function getHttpMethod(): string;

    /*
     * Statement Descriptor
     * Identification shown in customers credit card bill
     */
    public function setStatementDescriptor($value)
    {
        return $this->setParameter('statement_descriptor', $value);
    }

    public function getStatementDescriptor()
    {
        return $this->getParameter('statement_descriptor');
    }

    /*
     * Date of expiration
     * Useful only for Boleto
     */
    public function setDateOfExpiration($value)
    {
        return $this->setParameter('date_of_expiration', $value);
    }

    public function getDateOfExpiration()
    {
        return $this->getParameter('date_of_expiration');
    }

    /*
     * Purchased items
     */
    public function setItems($itemsArray)
    {
        return $this->setParameter('items', $itemsArray);
    }

    public function getItems()
    {
        $items =  $this->getParameter('items');

        return array_map(function ($item) {
            return [
                "id" => $item['id'],
                "title" => $item['name'],
                "picture_url" => $item['image_url'],
                "quantity" => $item['unities'],
                "unit_price" => $item['price'],
            ];
        }, $items);
    }

    /*
     * Payment installments
     * Useful only for credit card
     */
    public function setInstallments($value)
    {
        return $this->setParameter('installments', $value);
    }

    public function getInstallments()
    {
        $installments = $this->getParameter('installments');

        if(is_null($installments) || $installments === '') {
            $installments = 1;
        }

        return $installments;
    }

    /*
     * Credit card data
     * Object including token, payment_method_id and issuer_id
     */
    public function setCard($value)
    {
        return $this->setParameter('card', $value);
    }

    public function getCard()
    {
        return $this->getParameter('card');
    }

    /*
     * Purchase description
     */
    public function setDescription($value)
    {
        return $this->setParameter('description', $value);
    }

    public function getDescription()
    {
        return $this->getParameter('description');
    }

    /*
     * Additional info
     * Will include: IP address and items
     */
    public function setAdditionalInfo($value)
    {
        return $this->setParameter('additional_info', $value);
    }

    public function getAdditionalInfo()
    {
        return $this->getParameter('additional_info');
    }

    /*
     * User IP address
     */
    public function setIpAddress($value)
    {
        return $this->setParameter('ip_address', $value);
    }

    public function getIpAddress()
    {
        return $this->getParameter('ip_address');
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
     * Payer data
     * https://www.mercadopago.com.br/developers/pt/reference/payments/_payments/post
     */
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
            "last_name" => Arr::get($payer, 'last_name', ''),
            "date_registered" => Carbon::now()
        ];

        if ($phone = Arr::get($payer, 'phone')) {
            $data["phone"] = [
                "area_code" => Arr::get($phone, 'ddi', ''),
                "number" => Arr::get($phone, 'number', '')
            ];
        }

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
     * API REST base endpoint
     */
    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /*
     * General functions
     */
    public function toJSON($data, $options = 0)
    {
        return json_encode($data, $options | 64);
    }

}
