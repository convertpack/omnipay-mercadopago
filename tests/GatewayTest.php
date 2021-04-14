<?php

namespace Omnipay\MercadoPago;

use Illuminate\Support\Arr;
use Omnipay\MercadoPago\Message\CreateCardResponse;
use Omnipay\MercadoPago\Message\CreateCustomerResponse;
use Omnipay\MercadoPago\Message\FetchCustomerResponse;
use Omnipay\MercadoPago\Message\PurchaseResponse;
use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    protected $gateway;
    
    public function setUp()
    {
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setConfig(['access_token' => 'TEST-1542295155328669-032918-fcc8f2e291098c932751aa23cb2075e4-603862211', 'client_secret' => 'TEST-a18af9aa-b037-4ef9-922e-a9fec6101cb0']);

        $this->payer = [
            'email' => 'jhon@doe.com',
            'first_name' => 'Jhon',
            'last_name' => 'Doe',
            'phone' => [
                'ddi' => '55',
                'number' => '991234567',
            ],
            'document' => [
                'type' => 'CPF',
                'number' => '12345678900',
            ],
            'address' => [
                'zip_code' => '01234567',
                'street_name' => 'Rua Exemplo',
                'street_number' => '123 A',
            ]
        ];

        $this->items = [
            [
                'id' => 'PR0001',
                'name' => 'Point Mini',
                'description' => 'Producto Point para cobros con tarjetas mediante bluetooth',
                'image_url' => 'https://http2.mlstatic.com/resources/frontend/statics/growth-sellers-landings/device-mlb-point-i_medium@2x.png',
                'unities' => 1,
                'price' => 58.8
            ]
        ];
    }
    
    public function testPurchase()
    {
        $response = $this->gateway->purchase(['items' => $this->items])->send();
        
        $this->assertInstanceOf(PurchaseResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
    
    public function testCreateCustomer()
    {
        $response = $this->gateway->createCustomer(['payer' => $this->payer])->send();
        
        $this->assertInstanceOf(CreateCustomerResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testFetchCustomer()
    {
        $response = $this->gateway->fetchCustomer(['email' => 'jhon@doe.com'])->send();
        
        $this->assertInstanceOf(FetchCustomerResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testCreateCard()
    {
        $response = $this->gateway->createCard(['payer_id' => '1562188766852', 'card_token' => '9b2d63e00d66a8c721607214ceda233a'])->send();
        
        $this->assertInstanceOf(CreateCardResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}