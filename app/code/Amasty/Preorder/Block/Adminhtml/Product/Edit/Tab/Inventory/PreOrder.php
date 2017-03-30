<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

namespace Amasty\Preorder\Block\Adminhtml\Product\Edit\Tab\Inventory;
/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

use Magento\Backend\Block\Widget\Form\Generic;

class PreOrder extends Generic
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);

    }

    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        /** @var \Amasty\Preorder\Helper\Data $helper */
        $helper = $this->objectManager->get('Amasty\Preorder\Helper\Data');
        //$form->setHtmlIdPrefix('amasty_preorder');

        $fieldset = $form->addFieldset('amasty_preorder_fieldset', ['legend' => __('Pre-Order')]);

        $fieldset->addField(
            'amasty_preorder_note',
            'text',
            ['name' => 'product[amasty_preorder_note]', 'label' => __('Pre-Order Note'), 'title' => __('Pre-Order Note'), 'required' => false]
        );

        $fieldset->addField(
            'amasty_preorder_cart_label',
            'text',
            ['name' => 'product[amasty_preorder_cart_label]', 'label' => __('Pre-Order Cart Button'), 'title' => __('Pre-Order Cart Button'), 'required' => false]
        );
        $form->setValues($this->getProduct()->getData());

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
