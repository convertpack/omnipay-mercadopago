<?php

namespace Omnipay\MercadoPago\Message;

use Illuminate\Support\Arr;
use Omnipay\Common\Message\AbstractResponse as BaseAbstractResponse;

class AbstractResponse extends BaseAbstractResponse
{

    protected $errors = [
        'cc_rejected_bad_filled_card_number' => 'gateway.credit_card.invalid_number',
        'cc_rejected_bad_filled_date' => 'gateway.credit_card.invalid_exp_date',
        'cc_rejected_bad_filled_other' => 'gateway.credit_card.invalid_data',
        'cc_rejected_bad_filled_security_code' => 'gateway.credit_card.invalid_cvc',
        'cc_rejected_blacklist' => 'gateway.credit_card.rejected_by_fraud_prevention',
        'cc_rejected_call_for_authorize' => 'gateway.credit_card.rejected_by_bank',
        'cc_rejected_card_disabled' => 'gateway.credit_card.card_disabled',
        'cc_rejected_card_error' => 'gateway.credit_card.generic_error',
        'cc_rejected_duplicated_payment' => 'gateway.credit_card.duplicated_charge',
        'cc_rejected_high_risk' => 'gateway.credit_card.rejected_by_fraud_prevention',
        'cc_rejected_insufficient_amount' => 'gateway.credit_card.insufficient_balance',
        'cc_rejected_invalid_installments' => 'gateway.credit_card.invalid_installments',
        'cc_rejected_max_attemens' => 'gateway.credit_card.rate_limit',
        'cc_rejected_other_reason' => 'gateway.credit_card.generic_error'
    ];

    public function isSuccessful()
    {
        return $this->data['is_success'];
    }

    /**
     * Returns error message
     *
     * @return int
     */
    public function getMessage(): string
    {
        $message = Arr::get($this->data, 'data.message', '');

        return $message;
    }

    /**
     * Returns status code request
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int) Arr::get($this->data, 'status_code', 0);
    }

    public function getError(): array
    {
        $error = Arr::get($this->data, 'data.status_detail', '');
        
        return [
            'code' => Arr::first($this->errors, fn ($errorItem, $key) => $key === $error),
            'raw' => $error
        ];
    }
}
