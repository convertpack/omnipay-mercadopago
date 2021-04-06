<?php

namespace Omnipay\MercadoPago\Message;

use Carbon\Carbon;
use Omnipay\Common\Message\AbstractResponse;

/**
 * Complete Payment Response
 */
class ValidateIntegrationResponse extends AbstractResponse
{
    /*
     * Is this complete purchase response successful? Return true if status is approved
     * @return bool
     */
    public function isSuccessful()
    {
        return isset($this->data->status);
    }
}
