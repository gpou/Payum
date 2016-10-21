<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\Api\ObtainCardAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainCard;

class ObtainCardActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(ObtainCardAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainCardAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new ObtainCardAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new ObtainCardAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportObtainCardRequestWithArrayAccessModel()
    {
        $action = new ObtainCardAction();

        $this->assertTrue($action->supports(new ObtainCard(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportObtainCardRequestWithNotArrayAccessModel()
    {
        $action = new ObtainCardAction();

        $this->assertFalse($action->supports(new ObtainCard(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotObtainCardRequest()
    {
        $action = new ObtainCardAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ObtainCardAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new ObtainCardAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfModelAlreadyHaveCardSet()
    {
        $model = new \ArrayObject(['card' => 'aCard']);

        $action = new ObtainCardAction();

        $action->execute(new ObtainCard($model));
        $this->assertEquals('aCard', $model['card']);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfHttpRequestNotPOST()
    {
        $model = new \ArrayObject();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'GET';
            }))
        ;

        $action = new ObtainCardAction();
        $action->setGateway($gatewayMock);

        $action->execute(new ObtainCard($model));
        $this->assertArrayNotHasKey('card', $model);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfHttpRequestPOSTButNotContainStripeCard()
    {
        $model = array();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
            }))
        ;

        $action = new ObtainCardAction();
        $action->setGateway($gatewayMock);

        $action->execute(new ObtainCard($model));
        $this->assertArrayNotHasKey('card', $model);
    }

    /**
     * @test
     */
    public function shouldSetCardFromHttpRequestToObtainCardRequestOnPOST()
    {
        $model = array();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request = array('stripeCard' => 'theCard');
            }))
        ;

        $action = new ObtainCardAction();
        $action->setGateway($gatewayMock);

        $action->execute($obtainCard = new ObtainCard($model));

        $model = $obtainCard->getModel();
        $this->assertEquals('theCard', $model['card']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
