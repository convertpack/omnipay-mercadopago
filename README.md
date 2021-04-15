# Omnipay: Mercado Pago

**Mercado Pago driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 7.4+. This package implements MercadoPago support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply command:

`composer require convertpack/omnipay-mercado-pago`

## Basic Usage

The following gateways are provided by this package:

* MercadoPago

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## Examples

### Credit card

```php
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

$data = [
    'transaction_id' => 'CPK-1234567890',
    'payer' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@convertpack.io',
        'phone' => [
            'area_code': '11',
            'number' => '991919191',
        ],
        'document' => [
            'type' => 'CPF',
            'number' => '94530794130',
        ],
     ],
    'description' => 'Book purchase: The Power of Habit - Charles Duhigg',
    'ip_address' => '127.0.0.1',
    'notify_url' => 'https://api.foo.bar/webhook',
    'items' => [
        [
            'id' => 'P007EJSMC8',
            'name' => 'The Power of Habit - Charles Duhigg',
            'image_url' => 'https://cdn.foo.bar/images/P007EJSMC8.png',
            'unities' => 1,
            'price' => 59.90
        ]
    ],
    'payment_method' => 'credit_card',
    'card' => [
        'token' => '1A2B3C4D5E6F7G8H9I0J',
        'payment_method_id' => 'visa',
        'issuer_id' => '24',
    ],
    'date_of_expiration' => null,
    'installments' => 6,
    'statement_descriptor' => 'Super Books Inc.',
    'amount' => 59.99,
    
];

$response = $omnipay->purchase($data)->send();

if ($response->isSuccessful()) {
    return $response->getData();
}

return $response->getMessage();
```

### Boleto
```php
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

$data = [
    // ...same data as previous example
    'payment_method' => 'boleto',
    'card' => null,
    'date_of_expiration' => '2025-12-01',
    'installments' => 1,
    'statement_descriptor' => 'Super Books Inc.',
    'amount' => 59.99,
];

$response = $omnipay->purchase($data)->send();

if ($response->isSuccessful()) {
    return $response->getData();
}

return $response->getMessage();
```

### Create Customer

```php
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

$data = [
    'payer' => [
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
     ]
];

$response = $omnipay->createCustomer($data)->send();

if ($response->isSuccessful()) {
    return $response->getData();
}

return $response->getMessage();
```

### Find or Create Customer

```php
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

$data = [
    'payer' => [
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
     ]
];

$response = $omnipay->findOrCreateCustomer($data)->send();

if ($response->isSuccessful()) {
    return $response->getData();
}

return $response->getMessage();
```

### Find Customer

```php
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

$response = $omnipay->findCustomer(['email' => 'jhon@doe.com'])->send();

if ($response->isSuccessful()) {
    return $response->getData();
}

return $response->getMessage();
```

### Create Card

```php
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

$response = $omnipay->createCard(['payer_id' => '1562188766852', 'card_token' => '9b2d63e00d66a8c721607214ceda233a'])->send();

if ($response->isSuccessful()) {
    return $response->getData();
}

return $response->getMessage();
```
## Tests

How to run tests

`php ./vendor/bin/phpunit`

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/convertpack/omnipay-mercado-pago/issues),
or better yet, fork the library and submit a pull request.
