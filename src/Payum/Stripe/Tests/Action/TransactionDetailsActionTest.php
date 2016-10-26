<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Request\GetTransactionDetails;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\TransactionDetailsAction;
use Payum\Stripe\Constants;

class TransactionDetailsTest extends GenericActionTest
{
    protected $requestClass = GetTransactionDetails::class;

    protected $actionClass = TransactionDetailsAction::class;

    /**
     * @test
     */
    public function shouldSupportGetTransactionDetailsRequestWithArrayAccessModel()
    {
        $action = new TransactionDetailsAction();

        $this->assertTrue($action->supports(new GetTransactionDetails(array())));
    }

    /**
     * @test
     */
    public function shouldSupportGetTransactionDetailsRequestWithNonArrayAccessModel()
    {
        $action = new TransactionDetailsAction();

        $this->assertFalse($action->supports(new GetTransactionDetails(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldTransactionId()
    {
        $action = new TransactionDetailsAction();

        $model = [
            'id' => 'ch_1234567890',
        ];

        $action->execute($transactionDetails = new GetTransactionDetails($model));

        $this->assertEquals("ch_1234567890", $transactionDetails->getTransactionId());
    }

    /**
     * @test
     */
    public function shouldTransactionIdFromError()
    {
        $action = new TransactionDetailsAction();

        $model = [
           'error' => [
                'charge' => 'ch_1234567890',
            ]
        ];

        $action->execute($transactionDetails = new GetTransactionDetails($model));

        $this->assertEquals("ch_1234567890", $transactionDetails->getTransactionId());
    }

}
