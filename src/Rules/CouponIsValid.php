<?php

namespace SteadfastCollective\CashierExtended\Rules;

use Illuminate\Contracts\Validation\Rule;
use SteadfastCollective\CashierExtended\Contracts\Repositories\CouponRepository;

class CouponIsValid implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return resolve(CouponRepository::class)->show($value) !== null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'That coupon \':input\' is not valid.';
    }
}
