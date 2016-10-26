<?php
namespace Payum\Core\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

interface GetTransactionInfoInterface extends ModelAwareInterface, ModelAggregateInterface
{
    /**
     * @return mixed
     */
    public function getTransactionId();

}
