<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model;

use Magento\Framework\DB\Ddl\Table;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class InstallSchema
{
    /**
     * Retrieve module fields data array
     *
     * @return array
     */
    public function getData()
    {
        $dataArray = [
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_IS_DEFAULT,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'after'     => 'sort_order',
                    'comment'   => 'Is Default Value Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_COST,
                'params' => [
                    'type'      => Table::TYPE_DECIMAL,
                    'length'    => '10,2',
                    'unsigned'  => false,
                    'nullable'  => true,
                    'after'     => 'sort_order',
                    'comment'   => 'Cost (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option_type_value',
                'field_name' => Helper::KEY_WEIGHT,
                'params' => [
                    'type'      => Table::TYPE_DECIMAL,
                    'length'    => '10,2',
                    'unsigned'  => false,
                    'nullable'  => true,
                    'after'     => 'sort_order',
                    'comment'   => 'Weight (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_QTY_INPUT,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'after'     => 'sort_order',
                    'comment'   => 'Qty Input Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_option',
                'field_name' => Helper::KEY_ONE_TIME,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'after'     => 'sort_order',
                    'comment'   => 'One Time Option Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_entity',
                'field_name' => Helper::KEY_ABSOLUTE_COST,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'after'     => 'updated_at',
                    'comment'   => 'Absolute Cost Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_entity',
                'field_name' => Helper::KEY_ABSOLUTE_WEIGHT,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'after'     => 'updated_at',
                    'comment'   => 'Absolute Weight Flag (added by MageWorx Option Features)',
                ]
            ],
            [
                'table_name' => 'catalog_product_entity',
                'field_name' => Helper::KEY_ABSOLUTE_PRICE,
                'params' => [
                    'type'      => Table::TYPE_BOOLEAN,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => 0,
                    'after'     => 'updated_at',
                    'comment'   => 'Absolute Price Flag (added by MageWorx Option Features)',
                ]
            ],
        ];

        return $dataArray;
    }
}
