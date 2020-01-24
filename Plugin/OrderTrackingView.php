<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Magento\Shipping\Block\Adminhtml\Order\Tracking\View;
use Chazki\ChazkiArg\Model\ChazkiArg;

class OrderTrackingView
{
    /**
     * @param View $subject
     * @param $result
     * @param $code
     * @return \Magento\Framework\Phrase
     */
    public function afterGetCarrierTitle(View $subject, $result, $code)
    {
        return $code === ChazkiArg::TRACKING_CODE ? __(ChazkiArg::TRACKING_LABEL) : __('Custom Value');
    }
}
