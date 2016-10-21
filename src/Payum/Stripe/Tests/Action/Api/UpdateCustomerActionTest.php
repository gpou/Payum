<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\UpdateCustomerAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\UpdateCustomer;

class UpdateCustomerActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(UpdateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(UpdateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new UpdateCustomerAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new UpdateCustomerAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new UpdateCustomerAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCaptureCustomerRequestWithArrayAccessModel()
    {
        $action = new UpdateCustomerAction();

        $this->assertTrue($action->supports(new UpdateCustomer(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureCustomerRequestWithNotArrayAccessModel()
    {
        $action = new UpdateCustomerAction();

        $this->assertFalse($action->supports(new UpdateCustomer(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureCustomerRequest()
    {
        $action = new UpdateCustomerAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action UpdateCustomerAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new UpdateCustomerAction();

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
        $action = new UpdateCustomerAction();

        $action->execute(new UpdateCustomer(['default_source' => 'cardId']));
    }


    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The default_source fields are required.
     */
    public function throwIfDefaultSourceNotSet()
    {
        $action = new UpdateCustomerAction();

        $action->execute(new UpdateCustomer(['id' => 'customerId']));
    }
}
