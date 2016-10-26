<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Request\GetTransactionInfo;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\TransactionInfoAction;
use Payum\Stripe\Constants;

class TransactionInfoTest extends GenericActionTest
{
    protected $requestClass = GetTransactionInfo::class;

    protected $actionClass = TransactionInfoAction::class;

    /**
     * @test
     */
    public function shouldSuppoInfoRequestWithArrayAccessModel()
    {
        $action = new TransactionInfoAction();

        $this->assertTrue($action->supports(new GetTransactionInfo(array())));
    }

    /**
     * @test
     */
    public function shouldSuppoInfoRequestWithNonArrayAccessModel()
    {
        $action = new TransactionInfoAction();

        $this->assertFalse($action->supports(new GetTransactionInfo(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldTransactionId()
    {
        $action = new TransactionInfoAction();

        $model = [
            'id' => 'ch_1234567890',
        ];

        $action->execute($transactionInfo = new GetTransactionInfo($model));

        $this->assertEquals("ch_1234567890", $transactionInfo->getTransactionId());
    }

    /**
     * @test
     */
    public function shouldTransactionIdFromError()
    {
        $action = new TransactionInfoAction();

        $model = [
           'error' => [
                'charge' => 'ch_1234567890',
            ]
        ];

        $action->execute($transactionInfo = new GetTransactionInfo($model));

        $this->assertEquals("ch_1234567890", $transactionInfo->getTransactionId());
    }

}
