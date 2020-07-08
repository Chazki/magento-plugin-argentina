<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Observer;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * CheckoutProcessor constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if ($this->helperData->getEnabled()) {
            /** @var Order $order */
            $order = $observer->getEvent()->getData('order');

            /** @var Quote $quote */
            $quote = $observer->getEvent()->getData('quote');

            $shippingAddressData = $quote->getShippingAddress()->getData();
            if (isset($shippingAddressData[HelperData::REFERENCE_ATTRIBUTE_CODE])) {
                $order->getShippingAddress()->setData(
                    HelperData::REFERENCE_ATTRIBUTE_CODE,
                    $shippingAddressData[HelperData::REFERENCE_ATTRIBUTE_CODE]
                );
            }
        }

        return $this;
    }
}
