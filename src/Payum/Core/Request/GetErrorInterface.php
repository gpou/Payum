<?php
namespace Payum\Core\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

interface GetErrorInterface extends ModelAwareInterface, ModelAggregateInterface
{
    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getOriginalErrorCode();

    /**
     * @return mixed
     */
    public function setOriginalErrorCode($error_code);

    /**
     * @return void
     */
    public function markInvalidCreditCard();

    /**
     * @return void
     */
    public function markExpiredCreditCard();

    /**
     * @return void
     */
    public function markDeclinedCreditCard();

    /**
     * @return boolean
     */
    public function markMissingCreditCard();

    /**
     * @return boolean
     */
    public function markUnknown();

    /**
     * @return boolean
     */
    public function isInvalidCreditCard();

    /**
     * @return boolean
     */
    public function isExpiredCreditCard();

    /**
     * @return boolean
     */
    public function isDeclinedCreditCard();

    /**
     * @return boolean
     */
    public function isMissingCreditCard();

    /**
     * @return boolean
     */
    public function isUnknown();
}
