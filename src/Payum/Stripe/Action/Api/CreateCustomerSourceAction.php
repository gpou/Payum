<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCustomerSource;
use Stripe\Customer;
use Stripe\Error;
use Stripe\Stripe;

class CreateCustomerSourceAction extends GatewayAwareAction implements ApiAwareInterface
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
            'source'
        ));

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $customer = Customer::retrieve($model['id']);
            $createdCard = $customer->sources->create(array("card" => $model['source']));

            if (!$createdCard->id) {
                $model->replace($createdCard->__toArray(true));
            } else {
                $updatedCustomer = Customer::retrieve($model['id']);
                $updatedCustomer->default_source = $createdCard->id;
                $updatedCustomer->save();

                $model->replace($updatedCustomer->__toArray(true));
            }
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
            $request instanceof CreateCustomerSource &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
