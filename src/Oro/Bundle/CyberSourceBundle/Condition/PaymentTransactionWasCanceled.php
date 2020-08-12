<?php

namespace Oro\Bundle\CyberSourceBundle\Condition;

use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;

/**
 * Oro action condition for checking canceled payment transaction
 */
class PaymentTransactionWasCanceled extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    const NAME = 'payment_transaction_cybersource_was_canceled';

    /** @var PaymentTransaction */
    protected $transaction;

    /** @var PaymentTransactionRepository */
    protected $transactionRepository;

    /**
     * @param PaymentTransactionRepository $transactionRepository
     */
    public function __construct(PaymentTransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (array_key_exists('transaction', $options)) {
            $this->transaction = $options['transaction'];
        }

        if (!$this->transaction) {
            throw new InvalidArgumentException('Missing "transaction" option');
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function isConditionAllowed($context)
    {
        /** @var PaymentTransaction $transaction */
        $transaction = $this->resolveValue($context, $this->transaction);

        $cancelTransactions = $this->transactionRepository->findSuccessfulRelatedTransactionsByAction(
            $transaction,
            CyberSourcePaymentMethod::CANCEL
        );

        return !empty($cancelTransactions);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
