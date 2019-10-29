<?php
/**
 * Copyright © 2019 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Setup;

use Magento\Directory\Helper\Data;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var Data
     */
    protected $directoryData;

    /**
     * InstallData constructor.
     * @param Data $directoryData
     */
    public function __construct(Data $directoryData)
    {
        $this->directoryData = $directoryData;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            ['country_id' => 'AR', 'code' => 'BA',   'default_name' => 'Buenos Aires'],
            ['country_id' => 'AR', 'code' => 'CABA', 'default_name' => 'Ciudad Autónoma de Buenos Aires'],
            ['country_id' => 'AR', 'code' => 'CT',   'default_name' => 'Catamarca'],
            ['country_id' => 'AR', 'code' => 'CC',   'default_name' => 'Chaco'],
            ['country_id' => 'AR', 'code' => 'CH',   'default_name' => 'Chubut'],
            ['country_id' => 'AR', 'code' => 'CD',   'default_name' => 'Córdoba'],
            ['country_id' => 'AR', 'code' => 'CR',   'default_name' => 'Corrientes'],
            ['country_id' => 'AR', 'code' => 'ER',   'default_name' => 'Entre Ríos'],
            ['country_id' => 'AR', 'code' => 'FO',   'default_name' => 'Formosa'],
            ['country_id' => 'AR', 'code' => 'JY',   'default_name' => 'Jujuy'],
            ['country_id' => 'AR', 'code' => 'LP',   'default_name' => 'La Pampa'],
            ['country_id' => 'AR', 'code' => 'LR',   'default_name' => 'La Rioja'],
            ['country_id' => 'AR', 'code' => 'MZ',   'default_name' => 'Mendoza'],
            ['country_id' => 'AR', 'code' => 'MN',   'default_name' => 'Misiones'],
            ['country_id' => 'AR', 'code' => 'NQ',   'default_name' => 'Neuquén'],
            ['country_id' => 'AR', 'code' => 'RN',   'default_name' => 'Río Negro'],
            ['country_id' => 'AR', 'code' => 'SA',   'default_name' => 'Salta'],
            ['country_id' => 'AR', 'code' => 'SJ',   'default_name' => 'San Juan'],
            ['country_id' => 'AR', 'code' => 'SL',   'default_name' => 'San Luis'],
            ['country_id' => 'AR', 'code' => 'SC',   'default_name' => 'Santa Cruz'],
            ['country_id' => 'AR', 'code' => 'SF',   'default_name' => 'Santa Fe'],
            ['country_id' => 'AR', 'code' => 'SE',   'default_name' => 'Santiago del Estero'],
            ['country_id' => 'AR', 'code' => 'TF',   'default_name' => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur'],
            ['country_id' => 'AR', 'code' => 'TM',   'default_name' => 'Tucumán']
        ];

        foreach ($data as $row) {
            $bind = [
                'country_id' => $row['country_id'],
                'code' => $row['code'],
                'default_name' => $row['default_name']
            ];

            $setup->getConnection()->insert(
                $setup->getTable('directory_country_region'),
                $bind
            );

            $regionId = $setup->getConnection()->lastInsertId(
                $setup->getTable('directory_country_region')
            );

            $bind = [
                'locale' => 'en_US',
                'region_id' => $regionId,
                'name' => $row['default_name']
            ];

            $setup->getConnection()->insert(
                $setup->getTable('directory_country_region_name'),
                $bind
            );
        }
    }
}
