<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ConfirmPayment;

class ConfirmPaymentAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var Keys
     */
    protected $keys;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

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
        /** @var $request ObtainToken */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(array(
            'id',
        ));

        if (false == $model['paid']) {
            throw new LogicException('The charge must have been authorized.');
        }
        if (true == $model['captured']) {
            throw new LogicException('The charge has already been captured.');
        }
        if (true == $model['refunded']) {
            throw new LogicException('The charge has been refunded.');
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['confirm'])) {
            return;
        }

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, array(
            'model' => $model,
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        )));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ConfirmPayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
