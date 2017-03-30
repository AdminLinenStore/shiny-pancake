<?php
namespace Swissup\Askit\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\LayoutFactory;

use Swissup\Askit\Api\Data\ItemInterface;

class Grid extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('item_type_id', false);

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $grid = $resultLayout->getLayout()->getBlock('askit_question_listing');
        $grid->setUseAjax(true);
        switch ($type) {
            case 'customer':
                $customerId = $this->getRequest()->getParam('customer_id', false);
                $grid->setCustomerId($customerId);
                break;
            case ItemInterface::TYPE_CMS_PAGE:
                $pageId = $this->getRequest()->getParam('page_id', false);
                $grid->setPageId($pageId);
                break;
            case ItemInterface::TYPE_CATALOG_CATEGORY:
                $categoryId = $this->getRequest()->getParam('id', false);
                $grid->setCategoryId($categoryId);
                break;
            case ItemInterface::TYPE_CATALOG_PRODUCT:
            default:
                $productId = $this->getRequest()->getParam('id', false);
                $grid->setProductId($productId);
                break;
        }
        return $resultLayout;
    }
}