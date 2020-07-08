<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Chazki\ChazkiArg\Helper\Data as HelperData;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.3.6', '<')) {
            $connection = $setup->getConnection();

            $connection->addColumn(
                $setup->getTable('quote_address'),
                HelperData::REFERENCE_ATTRIBUTE_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Reference Text',
                    'after' => 'fax'
                ]
            );

            $connection->addColumn(
                $setup->getTable('sales_order_address'),
                HelperData::REFERENCE_ATTRIBUTE_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Reference Text',
                    'after' => 'fax'
                ]
            );
        }

        $setup->endSetup();
    }
}
