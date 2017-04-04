<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('catalog_product_option'),
                'group_option_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'after' => 'product_id',
                    'comment' => 'Group Option Id (added by MageWorx OptionTemplates)',
                ]
            );

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('catalog_product_option_type_value'),
                'group_option_value_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'after' => 'option_id',
                    'comment' => 'Group Option Value Id (added by MageWorx OptionTemplates)',
                ]
            );

        /**
         * Create table 'mageworx_optiontemplates_group'
         */
        $tableGroup = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_optiontemplates_group')
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Group ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'default' => '',
            ],
            'Title'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => true,
            ],
            'Last Modify Date'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
            ],
            'Is Active'
        )->setComment('MageWorx OptionTemplates Option Group table');

        $installer->getConnection()->createTable($tableGroup);

        /**
         * Create table 'mageworx_optiontemplates_relation'
         */
        $tableGroupRelation = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_optiontemplates_relation')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'ID'
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Group ID'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Product ID'
        )->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Option ID'
        )->addIndex(
            $installer->getIdxName(
                'mageworx_optiontemplates_relation',
                ['group_id', 'option_id', 'product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['group_id', 'option_id', 'product_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_optiontemplates_relation',
                'group_id',
                'mageworx_optiontemplates_group',
                'group_id'
            ),
            'group_id',
            $installer->getTable('mageworx_optiontemplates_group'),
            'group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_optiontemplates_relation',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment('MageWorx OptionTemplates Product Options and Group relation table');

        $installer->getConnection()->createTable($tableGroupRelation);

        /**
         * Create table 'mageworx_optiontemplates_group_option'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('mageworx_optiontemplates_group_option')
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option ID'
            )
            ->addColumn(
                'group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => null],
                'Type'
            )
            ->addColumn(
                'is_require',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Is Required'
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'SKU'
            )
            ->addColumn(
                'max_characters',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Max Characters'
            )
            ->addColumn(
                'file_extension',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'File Extension'
            )
            ->addColumn(
                'image_size_x',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Image Size X'
            )
            ->addColumn(
                'image_size_y',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Image Size Y'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addIndex(
                $installer->getIdxName(
                    'mageworx_optiontemplates_group_option',
                    ['group_id']
                ),
                ['group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option',
                    'group_id',
                    'mageworx_optiontemplates_group',
                    'group_id'
                ),
                'group_id',
                $installer->getTable('mageworx_optiontemplates_group'),
                'group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'MageWorx OptionTemplates Option Groups Option Table'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mageworx_optiontemplates_group_option_price'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('mageworx_optiontemplates_group_option_price')
            )
            ->addColumn(
                'option_price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Price ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                7,
                ['nullable' => false, 'default' => 'fixed'],
                'Price Type'
            )
            ->addIndex(
                $installer->getIdxName(
                    'mageworx_optiontemplates_group_option_price',
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('mageworx_optiontemplates_group_option_price', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_price',
                    'option_id',
                    'mageworx_optiontemplates_group_option',
                    'option_id'
                ),
                'option_id',
                $installer->getTable('mageworx_optiontemplates_group_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('mageworx_optiontemplates_group_option_price', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'MageWorx OptionTemplates Option Groups Option Price Table'
            );
        $installer->getConnection()
            ->createTable($table);

        /**
         * Create table 'mageworx_optiontemplates_group_option_title'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('mageworx_optiontemplates_group_option_title')
            )
            ->addColumn(
                'option_title_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Title ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Title'
            )
            ->addIndex(
                $installer->getIdxName(
                    'mageworx_optiontemplates_group_option_title',
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('mageworx_optiontemplates_group_option_title', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_title',
                    'option_id',
                    'mageworx_optiontemplates_group_option',
                    'option_id'
                ),
                'option_id',
                $installer->getTable('mageworx_optiontemplates_group_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('mageworx_optiontemplates_group_option_title', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'MageWorx OptionTemplates Option Groups Option Title Table'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mageworx_optiontemplates_group_option_type_value'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('mageworx_optiontemplates_group_option_type_value')
            )
            ->addColumn(
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'SKU'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addIndex(
                $installer->getIdxName('mageworx_optiontemplates_group_option_type_value', ['option_id']),
                ['option_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_type_value',
                    'option_id',
                    'mageworx_optiontemplates_group_option',
                    'option_id'
                ),
                'option_id',
                $installer->getTable('mageworx_optiontemplates_group_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'MageWorx OptionTemplates Option Groups Option Type Value Table'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mageworx_optiontemplates_group_option_type_price'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('mageworx_optiontemplates_group_option_type_price')
            )
            ->addColumn(
                'option_type_price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type Price ID'
            )
            ->addColumn(
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Type ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                7,
                ['nullable' => false, 'default' => 'fixed'],
                'Price Type'
            )
            ->addIndex(
                $installer->getIdxName(
                    'mageworx_optiontemplates_group_option_type_price',
                    ['option_type_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_type_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('mageworx_optiontemplates_group_option_type_price', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_type_price',
                    'option_type_id',
                    'mageworx_optiontemplates_group_option_type_value',
                    'option_type_id'
                ),
                'option_type_id',
                $installer->getTable('mageworx_optiontemplates_group_option_type_value'),
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_type_price',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'MageWorx OptionTemplates Option Groups Option Type Price Table'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mageworx_optiontemplates_group_option_type_title'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('mageworx_optiontemplates_group_option_type_title')
            )
            ->addColumn(
                'option_type_title_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type Title ID'
            )
            ->addColumn(
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Type ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Title'
            )
            ->addIndex(
                $installer->getIdxName(
                    'mageworx_optiontemplates_group_option_type_title',
                    ['option_type_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_type_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('mageworx_optiontemplates_group_option_type_title', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_type_title',
                    'option_type_id',
                    'mageworx_optiontemplates_group_option_type_value',
                    'option_type_id'
                ),
                'option_type_id',
                $installer->getTable('mageworx_optiontemplates_group_option_type_value'),
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mageworx_optiontemplates_group_option_type_title',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'MageWorx OptionTemplates Option Groups Option Type Title Table'
            );
        $installer->getConnection()->createTable($table);

        $this->eventManager->dispatch('mageworx_option_templates_install');

        $installer->endSetup();
    }
}
