<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\UpdateCustomer;
use Stripe\Customer;
use Stripe\Error;
use Stripe\Stripe;

class UpdateCustomerAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var Keys
     */
    protected $keys;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Keys) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->keys = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateCustomer */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(array(
            'id',
            'default_source'
        ));

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $customer = Customer::retrieve($model['id']);
            $customer->default_source = $model['default_source'];
            $customer->save();

            $updatedCustomer = Customer::retrieve($model['id']);

            $model->replace($updatedCustomer->__toArray(true));
        } catch (Error\Base $e) {
            $model->replace($e->getJsonBody());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof UpdateCustomer &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
