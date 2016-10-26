<?php
namespace Payum\Core\Request;

class GetTransactionInfo extends Generic implements GetTransactionInfoInterface
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
