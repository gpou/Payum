<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\RetrieveChargeAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\RetrieveCharge;

class RetrieveChargeActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(RetrieveChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(RetrieveChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RetrieveChargeAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new RetrieveChargeAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new RetrieveChargeAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCaptureChargeRequestWithArrayAccessModel()
    {
        $action = new RetrieveChargeAction();

        $this->assertTrue($action->supports(new RetrieveCharge(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureChargeRequestWithNotArrayAccessModel()
    {
        $action = new RetrieveChargeAction();

        $this->assertFalse($action->supports(new RetrieveCharge(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureChargeRequest()
    {
        $action = new RetrieveChargeAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action RetrieveChargeAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new RetrieveChargeAction();

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
        $action = new RetrieveChargeAction();

        $action->execute(new RetrieveCharge([]));
    }
}
