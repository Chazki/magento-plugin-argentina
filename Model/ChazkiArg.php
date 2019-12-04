<?php
/**
 * Copyright © 2019 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Chazki\ChazkiArg\Model\Connect as ApiConnect;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Sales\Model\Order\Shipment\TrackFactory;

class ChazkiArg
{
    /**
     * @var ApiConnect
     */
    protected $connect;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * ChazkiArg constructor.
     * @param Connect $connect
     * @param HelperData $helperData
     * @param OrderRepositoryInterface $orderRepository
     * @param TrackFactory $trackFactory
     */
    public function __construct(
        ApiConnect $connect,
        HelperData $helperData,
        OrderRepositoryInterface $orderRepository,
        TrackFactory $trackFactory
    ) {
        $this->connect = $connect;
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
        $this->trackFactory = $trackFactory;
    }

    /**
     * @param Shipment $shipment
     * @return bool
     */
    public function createShipment($shipping)
    {
        if (!$this->helperData->getEnabled()) {
            return false;
        }

        $shippingAddress = $shipping->getShippingAddress();
        $items = $shipping->getItems();
        $order = $shipping->getOrder();

        if (!isset($shippingAddress) || !isset($items) || !count($items) || !isset($order)) {
            return false;
        }

        $shippingMethod = $order->getShippingMethod(true);
        $carrierMethod = $shippingMethod->getMethod();
        $shipmentType = '';

        if (strpos($carrierMethod, 'regular') !== false) {
            $shipmentType = 'Regular';
        } elseif (strpos($carrierMethod, 'express') !== false) {
            $shipmentType = 'Express';
        } elseif (strpos($carrierMethod, 'scheduled') !== false) {
            $shipmentType = 'Programado';
        }

        $product = [
            'description' => '',
            'quantity'    => 0,
            'value'       => 0,
            'weight'      => 0
        ];

        /** @var Item $item */
        foreach ($items as $item) {
            $product['description'] .= ' (' . $item->getQty() . ') ' . $item->getName() . ' +';
            $product['quantity']    += $item->getQty();
            $product['value']       += doubleval($item->getPrice());
            $product['weight']      += intval($item->getWeight());
        }

        /** substr to remove the last '+' */
        $product['description'] = substr($product['description'], 0, -2);

        $shipment = [
            'shipment' => [
                'type'    => $shipmentType,
                'product' => $product,
                'destination' => [
                        'address' => [
                            'line' => $shippingAddress->getData('street') . ', ' . $shippingAddress->getCity() . ', ' . $shippingAddress->getRegionCode() . ', ' . $shippingAddress->getCountryId(),
                        'zip' => $shippingAddress->getPostcode()
                    ],
                    'name' => $shippingAddress->getName(),
                    'phone' => $shippingAddress->getTelephone(),
                    'email' => $shippingAddress->getEmail()
                ]
            ]
        ];

        $response = json_decode($this->connect->createShipment($shipment), true);
        if (isset($response['message']) && isset($response['data'])) {
            $msg = 'Error while sending Chazki shipment.';
            $order->addCommentToStatusHistory($msg);
            $this->orderRepository->save($order);

            $this->helperData->log('Shipment for order #' . $order->getIncrementId() . ' error while sending to Chazki.');
            $this->helperData->log(print_r($response, true));

            return false;
        } else {
            $trackData = [
                'carrier_code' => $shippingMethod->getData('carrier_code'),
                'title' => 'Chazki Track',
                'number' => $response['shipment']['tracking'],
            ];

            $track = $this->trackFactory->create()->addData($trackData);

            $shipping->addTrack($track)->save();

            $msg = 'Chazki shipment was created successfully - Track ID: ' . $response['shipment']['tracking'];

            $order->addCommentToStatusHistory($msg);
            $this->orderRepository->save($order);

            $this->helperData->log('Shipment for order #' . $order->getIncrementId() . ' sent to Chazki successfully - Track ID: ' . $response['shipment']['tracking']);
            $this->helperData->log(print_r($response, true));

            return true;
        }
    }

    /**
     * @param $workingOrder
     * @return mixed
     */
    public function sendOrderCancellation($workingOrder)
    {
        return $this->connect->sendOrderCancellation($workingOrder);
    }

    /**
     * Return config of module status
     *
     * @return mixed
     */
    public function isModuleEnable()
    {
        return $this->helperData->getEnabled();
    }
}
