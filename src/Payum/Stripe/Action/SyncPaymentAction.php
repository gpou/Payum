<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Stripe\Request\Api\RetrieveCharge;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;

class SyncPaymentAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['id']) {
            $this->gateway->execute(new RetrieveCharge($request->getModel()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getModel()->offsetExists('id')
        ;
    }
}
