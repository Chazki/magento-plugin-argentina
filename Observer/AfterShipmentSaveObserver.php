<?php
/**
 * Copyright © 2019 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Observer;

use Chazki\ChazkiArg\Model\ChazkiArg;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;

class AfterShipmentSaveObserver implements ObserverInterface
{
    /**
     * @var ChazkiArg
     */
    protected $chazkiArg;

    /**
     * AfterShipmentSaveObserver constructor.
     * @param ChazkiArg $chazkiArg
     */
    public function __construct(
        ChazkiArg $chazkiArg
    ) {
        $this->chazkiArg = $chazkiArg;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Shipment $shipping */
        $shipping = $observer->getEvent()->getShipment();
        $order = $shipping->getOrder();
        $shippingMethod = $order->getShippingMethod(true);
        $carrierCode = $shippingMethod->getData('carrier_code');

        if ($carrierCode === 'chazki') {
            $this->chazkiArg->createShipment($shipping);
        }
    }
}
