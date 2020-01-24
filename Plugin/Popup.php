<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Model\ChazkiArg;
use Magento\Shipping\Block\Tracking\Popup as OriginalPopup;

class Popup
{
    /**
     * @var ChazkiArg 
     */
    protected $chazkiArg;
    
    /**
     * Popup constructor.
     * @param ChazkiArg $chazkiArg
     */
    public function __construct(
        ChazkiArg $chazkiArg
    ) {
        $this->chazkiArg = $chazkiArg;
    }

    /**
     * @param OriginalPopup $subject
     * @param $result
     * @return mixed
     */
    public function afterGetTrackingInfo(OriginalPopup $subject, $result)
    {
        $chazkiLabel = __(ChazkiArg::TRACKING_LABEL)->getText();
        foreach ($result as $key => $values) {
            foreach ($values as $keys => $info) {
                if (is_array($info) && $info['title'] === $chazkiLabel) {
                    $tracking[$keys] = new \Magento\Framework\DataObject();

                    if (isset($info['number'])) {
                        $tracking[$keys]->setTracking($info['number']);

                        try {
                            $trackingData = $this->chazkiArg->getShipment($info['number']);
                            $trackingData = json_decode($trackingData, true);

                            if(isset($trackingData) && isset($trackingData['shipment']) && isset($trackingData['shipment']['history'])) {
                                $history = [];

                                foreach ($trackingData['shipment']['history'] as $data) {
                                    $history[] = [
                                        'deliverydate' => date('Y-m-d', strtotime($data['date'])),
                                        'deliverytime' => date('H:i:s', strtotime($data['date'])),
                                        'deliverylocation' => $data['location'],
                                        'activity' => $data['description']
                                    ];
                                }

                                $tracking[$keys]->setProgressdetail($history);
                            }
                        } catch (\Exception $e) {
                            /** Continue */
                        }
                    }

                    if (isset($info['title'])) {
                        $tracking[$keys]->setCarrierTitle($info['title']);
                    }

                    $result[$key] = $tracking;
                }
            }
        }
        return $result;
    }
}
