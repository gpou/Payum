<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CaptureChargeAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CaptureCharge;

class CaptureChargeActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CaptureChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureChargeAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CaptureChargeAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CaptureChargeAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCaptureChargeRequestWithArrayAccessModel()
    {
        $action = new CaptureChargeAction();

        $this->assertTrue($action->supports(new CaptureCharge(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureChargeRequestWithNotArrayAccessModel()
    {
        $action = new CaptureChargeAction();

        $this->assertFalse($action->supports(new CaptureCharge(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureChargeRequest()
    {
        $action = new CaptureChargeAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CaptureChargeAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CaptureChargeAction();

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
        $action = new CaptureChargeAction();

        $action->execute(new CaptureCharge([]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge must have been authorized.
     */
    public function throwIfPaidNotSet()
    {
        $action = new CaptureChargeAction();

        $action->execute(new CaptureCharge(['id' => 'chargeId']));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge must have been authorized.
     */
    public function throwIfPaidIsFalse()
    {
        $action = new CaptureChargeAction();

        $action->execute(new CaptureCharge(['id' => 'chargeId', 'paid' => false]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge has already been captured.
     */
    public function throwIfCapturedIsTrue()
    {
        $action = new CaptureChargeAction();

        $action->execute(new CaptureCharge(['id' => 'chargeId', 'paid' => true, 'captured' => true]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge has been refunded.
     */
    public function throwIfRefundedIsTrue()
    {
        $action = new CaptureChargeAction();

        $action->execute(new CaptureCharge(['id' => 'chargeId', 'paid' => true, 'refunded' => true]));
    }
}
