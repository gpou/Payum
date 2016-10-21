<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\Api\ConfirmPaymentAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ConfirmPayment;

class ConfirmPaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(ConfirmPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(ConfirmPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTemplateAsFirstArgument()
    {
        new ConfirmPaymentAction('aTemplateName');
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportConfirmPaymentRequestWithArrayAccessModel()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $this->assertTrue($action->supports(new ConfirmPayment(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportConfirmPaymentRequestWithNotArrayAccessModel()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $this->assertFalse($action->supports(new ConfirmPayment(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotConfirmPaymentRequest()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ConfirmPaymentAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->execute(new \stdClass());
    }


    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The id fields are required.
     */
    public function throwIfIdNotSet()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->execute(new ConfirmPayment([]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge must have been authorized.
     */
    public function throwIfPaidNotSet()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->execute(new ConfirmPayment(['id' => 'chargeId']));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge must have been authorized.
     */
    public function throwIfPaidIsFalse()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->execute(new ConfirmPayment(['id' => 'chargeId', 'paid' => false]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge has already been captured.
     */
    public function throwIfCapturedIsTrue()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->execute(new ConfirmPayment(['id' => 'chargeId', 'paid' => true, 'captured' => true]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge has been refunded.
     */
    public function throwIfRefundedIsTrue()
    {
        $action = new ConfirmPaymentAction('aTemplateName');

        $action->execute(new ConfirmPayment(['id' => 'chargeId', 'paid' => true, 'refunded' => true]));
    }

    /**
     * @test
     */
    public function shouldRenderExpectedTemplateIfHttpRequestNotPOST()
    {
        $model = new \ArrayObject();
        $model['id'] = 'chargeId';
        $model['paid'] = true;
        $templateName = 'theTemplateName';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'GET';
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($templateName, $model) {
                $this->assertEquals($templateName, $request->getTemplateName());

                $context = $request->getParameters();
                $this->assertArrayHasKey('model', $context);

                $request->setResult('theContent');
            }))
        ;

        $action = new ConfirmPaymentAction($templateName);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new ConfirmPayment($model));
        } catch (HttpResponse $reply) {
            $this->assertEquals('theContent', $reply->getContent());

            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldRenderTemplateIfHttpRequestPOSTButNotContainConfirm()
    {
        $model = array();
        $model['id'] = 'chargeId';
        $model['paid'] = true;
        $templateName = 'aTemplateName';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
        ;

        $action = new ConfirmPaymentAction($templateName);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new ConfirmPayment($model));
        } catch (HttpResponse $reply) {
            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldGiveControllBackIfHttpRequestPostWithConfirm()
    {
        $model = array();
        $model['id'] = 'chargeId';
        $model['paid'] = true;
        $templateName = 'aTemplateName';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request = array('confirm' => 1);
            }))
        ;

        $action = new ConfirmPaymentAction($templateName);
        $action->setGateway($gatewayMock);

        $action->execute(new ConfirmPayment($model));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
