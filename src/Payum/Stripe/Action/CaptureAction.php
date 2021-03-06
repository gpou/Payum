<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\CaptureAuthorizedCharge;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;
use Payum\Stripe\Request\Api\ConfirmPayment;
use Payum\Stripe\Request\Api\CaptureCharge;
use Payum\Stripe\Request\Api\RetrieveCharge;
use Payum\Stripe\Constants;

class CaptureAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['status'] && Constants::STATUS_SUCCEEDED == $model['status'] && false == $model['captured']) {
            $this->gateway->execute(new RetrieveCharge($model));
            $this->gateway->execute(new ConfirmPayment($model));
            if (@$model['error']) {
                return;
            }
        } elseif ($model['status']) {
            return;
        }

        if ($model['customer']) {
        } else {
            if (false == $model['card']) {
                $obtainToken = new ObtainToken($request->getToken());
                $obtainToken->setModel($model);

                $this->gateway->execute($obtainToken);
                if ($model['status'] == Constants::STATUS_FAILED) {
                    return;
                }
            }

        }

        if ($model['status'] && Constants::STATUS_SUCCEEDED == $model['status'] && false == $model['captured']) {
            $this->gateway->execute(new CaptureCharge($model));
        } else {
            $this->gateway->execute(new CreateCharge($model));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
