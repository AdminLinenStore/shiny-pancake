<?php
namespace Swissup\Askit\Controller\Adminhtml\Answer;

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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param Context $context
     * @param LayoutFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        // $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // $resultRedirect->setRefererUrl();

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Swissup\Askit\Model\Item');
        if ($id) {
            $model->load($id);
        }
        $this->coreRegistry->register('askit_question', $model);

        $type = $this->getRequest()->getParam('item_type_id', false);

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $grid = $resultLayout->getLayout()->getBlock('askit_answer_listing');
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
