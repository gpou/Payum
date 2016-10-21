<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Model\CreditCard;
use Payum\Core\Model\Refund;
use Payum\Core\Model\RefundInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\ConvertRefundAction;

class ConvertRefundActionTest extends GenericActionTest
{
    protected $actionClass = ConvertRefundAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Refund(), 'array')),
            array(new $this->requestClass($this->getMock(RefundInterface::class), 'array')),
            array(new $this->requestClass(new Refund(), 'array', $this->getMock(TokenInterface::class))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Refund(), 'foobar')),
            array(new $this->requestClass($this->getMock(RefundInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertRefundToDetails()
    {
        $refund = new Refund();
        $refund->setOriginalTransactionId('originalTransactionId');
        $refund->setAmount(123);

        $action = new ConvertRefundAction();

        $action->execute($convert = new Convert($refund, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('amount', $details);
        $this->assertEquals(123, $details['amount']);

        $this->assertArrayHasKey('charge', $details);
        $this->assertEquals('originalTransactionId', $details['charge']);
    }

    /**
     * @test
     */
    public function shouldNotSetOptionalValuesIntoDetails()
    {
        $refund = new Refund();
        $refund->setOriginalTransactionId('originalTransactionId');

        $action = new ConvertRefundAction();

        $action->execute($convert = new Convert($refund, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayNotHasKey('amount', $details);

        $this->assertArrayHasKey('charge', $details);
        $this->assertEquals('originalTransactionId', $details['charge']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $refund = new Refund();
        $refund->setOriginalTransactionId('originalTransactionId');
        $refund->setAmount(123);
        $refund->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertRefundAction();

        $action->execute($convert = new Convert($refund, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
