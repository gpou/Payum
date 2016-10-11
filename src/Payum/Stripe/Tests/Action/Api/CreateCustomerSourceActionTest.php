<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateCustomerSourceAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCustomerSource;

class CreateCustomerSourceActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerSourceAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerSourceAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateCustomerSourceAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CreateCustomerSourceAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CreateCustomerSourceAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCreateCustomerSourceRequestWithArrayAccessModel()
    {
        $action = new CreateCustomerSourceAction();

        $this->assertTrue($action->supports(new CreateCustomerSource(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreateCustomerSourceRequestWithNotArrayAccessModel()
    {
        $action = new CreateCustomerSourceAction();

        $this->assertFalse($action->supports(new CreateCustomerSource(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCustomerSourceRequest()
    {
        $action = new CreateCustomerSourceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreateCustomerSourceAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreateCustomerSourceAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The customer fields are required.
     */
    public function throwIfCustomerNotSet()
    {
        $action = new CreateCustomerSourceAction();

        $action->execute(new CreateCustomerSource(['source' => 'cardId']));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The source fields are required.
     */
    public function throwIfSourceNotSet()
    {
        $action = new CreateCustomerSourceAction();

        $action->execute(new CreateCustomerSource(['customer' => 'customerId']));
    }
}
