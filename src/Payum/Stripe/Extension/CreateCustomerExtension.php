<?php
namespace Payum\Stripe\Extension;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Stripe\Constants;
use Payum\Stripe\Request\Api\CreateCustomer;
use Payum\Stripe\Request\Api\ObtainToken;
use Payum\Stripe\Request\Api\RetrieveCustomer;
use Payum\Stripe\Request\Api\UpdateCustomer;
use Payum\Stripe\Request\Api\ConfirmPayment;
use Payum\Stripe\Request\Api\CreateCustomerSource;
use Payum\Stripe\Request\Api\RetrieveToken;
use Payum\Stripe\Request\Api\ObtainCard;

class CreateCustomerExtension implements ExtensionInterface
{
    /**
     * @var Context $context
     */
    public function onPreExecute(Context $context)
    {
        /** @var Capture $request */
        $request = $context->getRequest();
        if (false == $request instanceof Capture) {
            return;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return;
        }

        $model = ArrayObject::ensureArrayObject($model);

        $this->retrieveCustomer($context->getGateway(), $model);
        $this->obtainCard($context->getGateway(), $model);
        $this->createCustomer($context->getGateway(), $model);
    }

    /**
     * @var Context $context
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * @var Context $context
     */
    public function onPostExecute(Context $context)
    {
        /** @var Capture $request */
        $request = $context->getRequest();
        if (false == $request instanceof ObtainToken) {
            return;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return;
        }

        $this->createCustomer($context->getGateway(), ArrayObject::ensureArrayObject($model));
    }

    /**
     * @param GatewayInterface $gateway
     * @param ArrayObject $model
     */
    protected function createCustomer(GatewayInterface $gateway, ArrayObject $model)
    {
        if (false == ($model['card'] && is_string($model['card']))) {
            return;
        }

        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');

        if (isset($customer['id'])) {
            if (substr($model['card'], 0, 3) == 'tok') {
                $token = ['token' => $model['card']];
                $token = ArrayObject::ensureArrayObject($token);
                $gateway->execute(new RetrieveToken($token));
                $token = $token->toUnsafeArray();
                if ($existingCard = current(array_filter(
                    @$customer['sources']['data'] ?: [],
                    function($source) use($token) {
                        return $source['fingerprint'] == $token['card']['fingerprint'];
                    }
                ))) {
                    $source = $existingCard['id'];
                } else {
                    $customer['source'] = $model['card'];
                    $gateway->execute(new CreateCustomerSource($customer));
                    $source = $customer['default_source'];
                }
            } else {
                $source = $model['card'];
            }
        } else {
            $customer['source'] = $model['card'];
            $gateway->execute(new CreateCustomer($customer));
            $source = $customer['default_source'];
        }

        $local['customer'] = $customer->toUnsafeArray();
        $model['local'] = $local->toUnsafeArray();
        unset($model['card']);

        if ($customer['id'] && !@$customer['error']) {
            $model['customer'] = $customer['id'];
            $model['source'] = $source;
        } else {
            $model['status'] = Constants::STATUS_FAILED;
            $model['error'] = $customer['error'];
        }
    }

    protected function obtainCard($gateway, $model)
    {
        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');
        if (false == $customer['id']) {
            return;
        }

        $gateway->execute(new ObtainCard($model));
    }

    protected function retrieveCustomer($gateway, $model)
    {
        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');
        if (false == $customer['id']) {
            return;
        }

        $customer = ArrayObject::ensureArrayObject($customer);
        $gateway->execute(new RetrieveCustomer($customer));

        $local['customer'] = $customer->toUnsafeArray();
        $model['local'] = $local->toUnsafeArray();
    }
}
