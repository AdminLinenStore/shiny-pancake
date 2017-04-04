<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model;

use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class Installer. Install custom fields from all APO package modules.
 * @package MageWorx\OptionBase\Model
 */
class Installer
{
    /**
     * Array of InstallSchema models from APO package.
     *
     * @var array
     */
    protected $installSchema;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Installer constructor.
     *
     * @param array $installSchema
     * @param ResourceConnection $resource
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ResourceConnection $resource,
        ModuleManager $moduleManager,
        $installSchema = []
    ) {
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->installSchema = $installSchema;
    }

    /**
     * This method adds custom fields to database.
     */
    public function install()
    {
        $installer = $this->resource;

        foreach ($this->installSchema as $module) {
            $data = $module->getData();

            foreach ($data as $field) {
                // install to Magento
                // Do not store the data in the catalog_product_entity
                if ($field['table_name'] != 'catalog_product_entity') {
                    $installer
                        ->getConnection()
                        ->addColumn(
                            $installer->getTableName($field['table_name']),
                            $field['field_name'],
                            $field['params']
                        );
                }

                // install to MageWorx OptionTemplates
                if ($this->moduleManager->isEnabled('MageWorx_OptionTemplates')) {
                    $mageworxTableName = str_replace(
                        'catalog_product_',
                        'mageworx_optiontemplates_group_',
                        $field['table_name']
                    );

                    if ($mageworxTableName == 'mageworx_optiontemplates_group_entity') {
                        $mageworxTableName = 'mageworx_optiontemplates_group';
                    }

                    if ($installer->getConnection()->isTableExists($mageworxTableName)) {
                        $installer
                            ->getConnection()
                            ->addColumn(
                                $installer->getTableName($mageworxTableName),
                                $field['field_name'],
                                $field['params']
                            );
                    }
                }
            }
        }
    }
}
