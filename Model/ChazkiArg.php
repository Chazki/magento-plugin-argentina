<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
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
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackCollectionFactory;
use Magento\Framework\App\ProductMetadataInterface;

class ChazkiArg
{
    const TRACKING_CODE = 'chazki_arg';
    const TRACKING_LABEL = 'Chazki - Argentina';
    
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
     * @var TrackCollectionFactory
     */
    protected $trackCollectionFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * ChazkiArg constructor.
     * @param Connect $connect
     * @param HelperData $helperData
     * @param OrderRepositoryInterface $orderRepository
     * @param TrackFactory $trackFactory
     * @param TrackCollectionFactory $trackCollectionFactory
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ApiConnect $connect,
        HelperData $helperData,
        OrderRepositoryInterface $orderRepository,
        TrackFactory $trackFactory,
        TrackCollectionFactory $trackCollectionFactory,
        ProductMetadataInterface $productMetadata
    ) {
        $this->connect = $connect;
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
        $this->trackFactory = $trackFactory;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipping
     * @return  bool
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

        if (strpos($carrierMethod, 'reg') !== false) {
            $shipmentType = 'Regular';
        } elseif (strpos($carrierMethod, 'exp') !== false) {
            $shipmentType = 'Express';
        } elseif (strpos($carrierMethod, 'sche') !== false) {
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

        $shippingTracks = $shipping->getTracks();
        if (isset($shippingTracks) && count($shippingTracks)) {
            foreach ($shippingTracks as $key => $track) {
                if ($track->getCarrierCode() === self::TRACKING_CODE) {
                    $shipment['shipment']['tracking'] = $track->getTrackNumber();
                    $keyTrackId = $key;
                    break;
                }
            }
        }

        $response = json_decode($this->connect->createShipment($shipment), true);
        if (isset($response['message']) && isset($response['data'])) {
            $msg = 'Error while sending Chazki shipment.';
            $order->addCommentToStatusHistory($msg);
            $this->orderRepository->save($order);

            $this->helperData->log('Shipment for order #' . $order->getIncrementId() . ' error while sending to Chazki.');
            $this->helperData->log(print_r($response, true));

            return false;
        } else {
            if (isset($shipment['shipment']['tracking']) && isset($keyTrackId)) {
                if (is_array($shippingTracks)) {
                    $shippingTracks[$keyTrackId]->setTrackNumber($response['shipment']['tracking']);
                } else {
                    $shippingTracks->getItems()[$keyTrackId]->setTrackNumber($response['shipment']['tracking']);
                }
            } else {
                if (version_compare($this->productMetadata->getVersion(), '2.2.2', '<')) {
                    $shippingTracks = $shipping->getTracks();

                    if (is_array($shippingTracks) && !count($shippingTracks)) {
                        $shipping->setTracks(
                            $this->trackCollectionFactory->create()->setShipmentFilter(
                                $shipping->getId()
                            )
                        );
                    }
                }

                $trackData = [
                    'carrier_code' => self::TRACKING_CODE,
                    'title' => __(self::TRACKING_LABEL),
                    'number' => $response['shipment']['tracking']
                ];

                $track = $this->trackFactory->create()->addData($trackData);
                $shipping->addTrack($track);
            }

            $shipping->save();

            $msg = 'Chazki shipment was created successfully - Track ID: ' . $response['shipment']['tracking'];

            $order->addCommentToStatusHistory($msg);
            $this->orderRepository->save($order);

            $this->helperData->log('Shipment for order #' . $order->getIncrementId() . ' sent to Chazki successfully - Track ID: ' . $response['shipment']['tracking']);
            $this->helperData->log(print_r($response, true));

            return true;
        }
    }

    /**
     * @param $trackingId
     * @return mixed
     */
    public function getShipment($trackingId)
    {
        return $this->connect->getShipment($trackingId);
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
