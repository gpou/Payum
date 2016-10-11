<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\RetrieveCustomerAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\RetrieveCustomer;

class RetrieveCustomerActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(RetrieveCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(RetrieveCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RetrieveCustomerAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new RetrieveCustomerAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new RetrieveCustomerAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCaptureCustomerRequestWithArrayAccessModel()
    {
        $action = new RetrieveCustomerAction();

        $this->assertTrue($action->supports(new RetrieveCustomer(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureCustomerRequestWithNotArrayAccessModel()
    {
        $action = new RetrieveCustomerAction();

        $this->assertFalse($action->supports(new RetrieveCustomer(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureCustomerRequest()
    {
        $action = new RetrieveCustomerAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action RetrieveCustomerAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new RetrieveCustomerAction();

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
        $action = new RetrieveCustomerAction();

        $action->execute(new RetrieveCustomer([]));
    }
}
