<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\Downloads\Observer\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\Downloads\Model\ResourceModel\Attachment;
use Magento\Framework\Registry;

class SaveProductAttachments implements ObserverInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var JsHelper
     */
    protected $jsHelper;

    /**
     * @var Attachment
     */
    protected $attachmentResource;

    /**
     *
     * @param Context $context
     * @param JsHelper $jsHelper
     * @param Registry $coreRegistry
     * @param Attachment $attachmentResource
     */
    public function __construct(
        Context $context,
        JsHelper $jsHelper,
        Registry $coreRegistry,
        Attachment $attachmentResource
    ) {
        $this->context            = $context;
        $this->jsHelper           = $jsHelper;
        $this->coreRegistry       = $coreRegistry;
        $this->attachmentResource = $attachmentResource;
    }


    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $post = $this->context->getRequest()->getPostValue('attachments', -1);
        if ($post != '-1') {
            $post = $this->jsHelper->decodeGridSerializedInput($post);
            $product = $this->coreRegistry->registry('product');
            $this->attachmentResource->saveAttachmentProductRelation($product, $post);
        }
        return $this;
    }
}
