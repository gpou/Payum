<?php
namespace Payum\Stripe;

interface StripeHeadersInterface
{
    /**
     * @param mixed $request
     */
    public function getStripeHeaders($request);
}
