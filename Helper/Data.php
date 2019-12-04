<?php
/**
 * Copyright © 2019 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * Data constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->_logger = $logger;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->scopeConfig->getValue('carriers/chazki_arg/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Custom Log
     *
     * @param $msg
     * @param bool $echo
     */
    public function log($msg, $echo = false)
    {
        $this->_logger->info($msg);

        if ($echo) {
            echo PHP_EOL . $msg;
        }
    }
}
