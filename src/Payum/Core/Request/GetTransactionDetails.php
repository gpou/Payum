<?php
namespace Payum\Core\Request;

class GetTransactionDetails extends Generic implements GetTransactionDetailsInterface
{
    protected $transaction_id;

    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

}
