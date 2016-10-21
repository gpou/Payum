<?php
namespace Payum\Stripe\Extension;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\SensitiveValue;
use Payum\Stripe\Constants;
use Payum\Stripe\Request\Api\CreateCustomer;
use Payum\Stripe\Request\Api\ObtainToken;
use Payum\Stripe\Request\Api\RetrieveCustomer;
use Payum\Stripe\Request\Api\UpdateCustomer;
use Payum\Stripe\Request\Api\CreateCustomerSource;
use Payum\Stripe\Request\Api\CreateCharge;

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
        if (false == $request instanceof ObtainToken
            && false == $request instanceof CreateCharge) {
            return;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return;
        }

        if ($request instanceof ObtainToken) {
            $this->createCustomer($context->getGateway(), ArrayObject::ensureArrayObject($model));
        }

        if ($request instanceof CreateCharge) {
            $this->updateCustomer($context->getGateway(), ArrayObject::ensureArrayObject($model));
        }
    }

    /**
     * @param GatewayInterface $gateway
     * @param ArrayObject $model
     */
    protected function createCustomer(GatewayInterface $gateway, ArrayObject $model)
    {
        if (@$model['customer']) {
            return;
        }

        if (!@$model['card']
            || (!is_string($model['card']) && !$model['card'] instanceof SensitiveValue)
        ) {
            return;
        }

        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');

        $customer['source'] = $model['card'] instanceof SensitiveValue
            ? $model['card']->peek()
            : $model['card'];
        if (@$customer['id']) {
            if ($model['card'] instanceof SensitiveValue || substr($model['card'], 0, 3) == 'tok') {
                $customerSource = ArrayObject::ensureArrayObject(['customer' => $customer['id'], 'source' => $customer['source']]);
                $gateway->execute(new CreateCustomerSource($customerSource));
                if (!@$customerSource['id']) {
                    $model['status'] = Constants::STATUS_FAILED;
                    $model['error'] = @$customerSource['error'];
                    return;
                }
                $model['source'] = $customerSource['id'];
            } else {
                $model['source'] = $model['card'];
            }
        } else {
            $gateway->execute(new CreateCustomer($customer));
            $model['source'] = $customer['default_source'];
        }

        $customer = $customer->toUnsafeArray();
        if ($model['card'] instanceof SensitiveValue) {
            unset($customer['source']);
        }
        $local['customer'] = $customer;
        $model['local'] = $local->toUnsafeArray();
        unset($model['card']);

        if (@$customer['id'] && !@$customer['error']) {
            $model['customer'] = $customer['id'];
        } else {
            $model['status'] = Constants::STATUS_FAILED;
            $model['error'] = $customer['error'];
        }
    }

    protected function retrieveCustomer($gateway, $model)
    {
        if (@$model['customer']) {
            return;
        }

        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');
        if (!@$customer['id']) {
            return;
        }

        $customer = ArrayObject::ensureArrayObject($customer);
        $gateway->execute(new RetrieveCustomer($customer));

        $local['customer'] = $customer->toUnsafeArray();
        $model['local'] = $local->toUnsafeArray();
    }

    protected function updateCustomer($gateway, $model)
    {
        if (!@$model['source'] || !@$model['customer']) {
            return;
        }

        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');
        if (!@$customer['id'] || @$customer['default_source'] == $model['source']) {
            return;
        }

        $customer = ArrayObject::ensureArrayObject($customer);
        $gateway->execute(new UpdateCustomer($customer));

        if (!@$customer['id']) {
            return;
        }

        $local['customer'] = $customer->toUnsafeArray();
        $model['local'] = $local->toUnsafeArray();
    }
}
