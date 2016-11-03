<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;

trait StripeHeadersTrait
{
    public function getStripeHeaders($request)
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        return @$model['local']['stripe_headers'] ?: [];
    }
}
