# Omnipay: MercadoPago

**MercadoPago driver for the Omnipay PHP payment processing library**

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

### Example

```
$omnipay = Omnipay::create('MercadoPago');

$omnipay->setAccessToken('{TOKEN}');

// Required define params by transaction captured
$purchase = [
    'payer' => [
       'first_name' => 'Test',
      'last_name' => 'Test',
      'phone' => [
        'area_code': 11,
        'number => '987654321'
      ],
      'address' => []
     ],
    'description' => 'Purchase descript...',
    'notification_url' => 'https://webhook.site/#id',
    'paymentMethod' => 'boleto',
    'items' => [
        [
            'id' => 1,
            'title' => 'Product test',
            'picture_url' => 'https://picsum.photos/400/400',
            'quantity' => 1,
            'unit_price' => (double) 8.07
        ]
    ],
    'ip_address' => '127.0.0.1',
    'statement_descriptor' => 'Company Test purchase',
    'amount' => (double) 8.07
];

$response = $omnipay->purchase($purchase)->send();

return $response->getData();
```

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/convertpack/omnipay-mercado-pago/issues),
or better yet, fork the library and submit a pull request.
