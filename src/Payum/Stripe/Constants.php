<?php
namespace Payum\Stripe;

class Constants
{
    const STATUS_SUCCEEDED = 'succeeded';

    const STATUS_PAID = 'paid';

    const STATUS_FAILED = 'failed';

    const OBJECT_CHARGE = 'charge';

    const OBJECT_REFUND = 'refund';

    private function __construct()
    {
    }
}