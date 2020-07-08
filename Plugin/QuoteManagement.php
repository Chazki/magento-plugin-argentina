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
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\QuoteManagement as OriginalQuoteManagement;

class QuoteManagement
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * QuoteManagement constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param OriginalQuoteManagement $subject
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit(
        OriginalQuoteManagement $subject,
        QuoteEntity $quote,
        $orderData = []
    ) {
        if ($this->helperData->getEnabled()) {
            $shippingAddress = $quote->getShippingAddress();

            if (isset($shippingAddress)) {
                $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();

                if (isset($shippingAddressExtensionAttributes)) {
                    $referenceNote = $shippingAddress->getData(HelperData::REFERENCE_ATTRIBUTE_CODE);
                    $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('set', HelperData::REFERENCE_ATTRIBUTE_CODE)}($referenceNote);
                    $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);
                }
            }
        }
    }
}
