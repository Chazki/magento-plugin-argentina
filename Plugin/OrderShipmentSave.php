<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;
use Chazki\ChazkiArg\Model\ChazkiArg;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

class OrderShipmentSave
{
    /**
     * @var RequestInterface 
     */
    protected $request;

    /**
     * @var ChazkiArg
     */
    protected $chazkiArg;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * OrderShipmentSave constructor.
     * @param RequestInterface $request
     * @param ChazkiArg $chazkiArg
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        RequestInterface $request,
        ChazkiArg $chazkiArg,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    )
    {
        $this->request = $request;
        $this->chazkiArg = $chazkiArg;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param Tracking $subject
     * @param $result
     * @return mixed
     */
    public function aroundExecute(Save $subject, callable $proceed)
    {
        $tracking = $this->request->getParam('tracking');

        if (isset($tracking) && count($tracking)) {
            foreach ($tracking as $track) {
                if ($track['carrier_code'] === ChazkiArg::TRACKING_CODE) {
                    $trackPrefix = $this->scopeConfig->getValue('shipping/chazki_arg/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $shipment = $this->chazkiArg->getShipment($trackPrefix . $track['number']);
                    $shipment = json_decode($shipment, true);

                    if(
                        isset($shipment) &&
                        isset($shipment['shipment']) &&
                        isset($shipment['shipment']['tracking']) &&
                        $shipment['shipment']['tracking'] === $trackPrefix . $track['number']
                    ) {
                        /** @var Redirect $resultRedirect */
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $this->messageManager->addErrorMessage(__("The tracking ID " . $trackPrefix . $track['number'] . " is already in use"));

                        return $resultRedirect->setPath('*/*/new', ['order_id' => $this->request->getParam('order_id')]);
                    }
                }
            }
        }
        
        return $proceed();
    }
}
