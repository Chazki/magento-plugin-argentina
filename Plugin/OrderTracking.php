<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Magento\Shipping\Block\Adminhtml\Order\Tracking;
use Chazki\ChazkiArg\Model\ChazkiArg;

class OrderTracking
{
    /**
     * @param Tracking $subject
     * @param $result
     * @return mixed
     */
    public function afterGetCarriers(Tracking $subject, $result)
    {
        $result[ChazkiArg::TRACKING_CODE] = __(ChazkiArg::TRACKING_LABEL);
        return $result;
    }
}
