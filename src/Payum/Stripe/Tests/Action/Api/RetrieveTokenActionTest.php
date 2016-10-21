<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\RetrieveTokenAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\RetrieveToken;

class RetrieveTokenActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(RetrieveTokenAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(RetrieveTokenAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RetrieveTokenAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new RetrieveTokenAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new RetrieveTokenAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCaptureTokenRequestWithArrayAccessModel()
    {
        $action = new RetrieveTokenAction();

        $this->assertTrue($action->supports(new RetrieveToken(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureTokenRequestWithNotArrayAccessModel()
    {
        $action = new RetrieveTokenAction();

        $this->assertFalse($action->supports(new RetrieveToken(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureTokenRequest()
    {
        $action = new RetrieveTokenAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action RetrieveTokenAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new RetrieveTokenAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The token fields are required.
     */
    public function throwIfTokenNotSet()
    {
        $action = new RetrieveTokenAction();

        $action->execute(new RetrieveToken([]));
    }
}
