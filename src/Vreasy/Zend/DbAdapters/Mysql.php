<?php
/**
 * Mysql PDO DB adapter
 *
 * Vreasy
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * @copyright   Copyright (c) 2017 Vreasy Ltd. (https://www.vreasy.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Vreasy\Zend;

class DbAdapters_Mysql extends \Zend_Db_Adapter_Pdo_Mysql
{

    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected $_transactionLevel = 0;

    /**
     * Whether transaction was rolled back or not
     *
     * @var bool
     */
    protected $_isRolledBack = false;

    /**
     * Begin new DB transaction for connection
     *
     * @return $this
     * @throws \Exception
     */
    public function beginTransaction()
    {
        if ($this->_isRolledBack) {
            throw new \RuntimeException('Rolled back transaction has not been completed correctly.');
        }
        if ($this->_transactionLevel === 0) {
            parent::beginTransaction();
        }
        ++$this->_transactionLevel;
        return $this;
    }

    /**
     * Commit DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function commit()
    {
        if ($this->_transactionLevel === 1 && !$this->_isRolledBack) {
            parent::commit();
        } elseif ($this->_transactionLevel === 0) {
            throw new \RuntimeException('Asymmetric transaction commit.');
        } elseif ($this->_isRolledBack) {
            throw new \RuntimeException('Rolled back transaction has not been completed correctly.');
        }
        --$this->_transactionLevel;
        return $this;
    }

    /**
     * Rollback DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function rollBack()
    {
        if ($this->_transactionLevel === 1) {
            parent::rollBack();
            $this->_isRolledBack = false;
        } elseif ($this->_transactionLevel === 0) {
            throw new \RuntimeException('Asymmetric transaction rollback.');
        } else {
            $this->_isRolledBack = true;
        }
        --$this->_transactionLevel;
        return $this;
    }

    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function getTransactionLevel()
    {
        return $this->_transactionLevel;
    }


    /**
     * Check if all transactions have been committed
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_transactionLevel > 0) {
            trigger_error('Some transactions have not been committed or rolled back', E_USER_ERROR);
        }
    }
}
