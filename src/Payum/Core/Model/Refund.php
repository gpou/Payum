<?php
namespace Payum\Core\Model;

class Refund implements RefundInterface
{
    /**
     * @var string
     */
    protected $originalTransactionId;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var array
     */
    protected $details;

    public function __construct()
    {
        $this->details = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalTransactionId()
    {
        return $this->originalTransactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setOriginalTransactionId($transactionId)
    {
        $this->originalTransactionId = $transactionId;
    }

    /**
     * {@inheritDoc}
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * {@inheritDoc}
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * {@inheritDoc}
     *
     * @param array|\Traversable $details
     */
    public function setDetails($details)
    {
        if ($details instanceof \Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }
}
