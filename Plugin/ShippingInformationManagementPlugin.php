<?php
/**
* Copyright © 2020 Chazki. All rights reserved.
*
* @category Class
* @package  Chazki_ChazkiArg
* @author   Chazki
*/

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\QuoteRepository;

class ShippingInformationManagementPlugin
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param QuoteRepository $quoteRepository
     * @param HelperData $helperData
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        HelperData $helperData
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helperData = $helperData;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($this->helperData->getEnabled()) {
            $shippingAddress = $addressInformation->getShippingAddress();
            $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();

            if (count($shippingAddressExtensionAttributes->__toArray())) {
                $referenceNote = $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('get', HelperData::REFERENCE_ATTRIBUTE_CODE)}();
                $shippingAddress->setData(HelperData::REFERENCE_ATTRIBUTE_CODE, $referenceNote);
            }
        }
    }
}
