<?php
namespace Payum\Core\Model;

/**
 * @method array getDetails()
 */
interface RefundInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @return string
     */
    public function getOriginalTransactionId();

    /**
     * @return int
     */
    public function getAmount();
}
