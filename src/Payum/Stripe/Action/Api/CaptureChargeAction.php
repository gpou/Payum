<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CaptureCharge;
use Stripe\Charge;
use Stripe\Error;
use Stripe\Stripe;

class CaptureChargeAction implements ActionInterface, ApiAwareInterface
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
        /** @var $request CreateCharge */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['paid']) {
            throw new LogicException('The charge must have been authorized.');
        }
        if (true == $model['captured']) {
            throw new LogicException('The charge has already been captured.');
        }

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $charge = Charge::retrieve($model['id']);
            $charge->capture();

            $model->replace($charge->__toArray(true));
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
            $request instanceof CaptureCharge &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
