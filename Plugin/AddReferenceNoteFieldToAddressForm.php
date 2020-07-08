<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Block\Address\Edit\Field\ReferenceNote;
use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Customer\Block\Address\Edit as Subject;

class AddReferenceNoteFieldToAddressForm
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AddReferenceNoteFieldToAddressForm constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Subject $subject
     * @param $html
     * @return string
     */
    public function afterToHtml(Subject $subject, $html)
    {
        if ($this->helperData->getEnabled()) {
            $referenceNoteBlock = $this->getChildBlock(ReferenceNote::class, $subject);
            $referenceNoteBlock->setAddress($subject->getAddress());
            $html = $this->appendBlockBeforeFieldsetEnd($html, $referenceNoteBlock->toHtml());
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $childHtml
     *
     * @return string
     */
    private function appendBlockBeforeFieldsetEnd($html, $childHtml)
    {
        $pregMatch = '/\<\/fieldset\>/';
        $pregReplace = $childHtml . '\0';
        $html = preg_replace($pregMatch, $pregReplace, $html, 1);

        return $html;
    }

    /**
     * @param $blockClass
     * @param $parentBlock
     *
     * @return mixed
     */
    private function getChildBlock($blockClass, $parentBlock)
    {
        return $parentBlock->getLayout()->createBlock($blockClass, basename($blockClass));
    }
}
