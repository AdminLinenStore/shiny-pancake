<?php
namespace Swissup\Askit\Controller\Adminhtml\Item;

use Swissup\Askit\Api\Data\ItemInterface;
use Magento\Framework\Controller\ResultFactory;
use Swissup\Askit\Model\ResourceModel\Item\Collection as AbstractCollection;

/**
 * Class MassStatus
 */
class MassStatus extends \Swissup\Askit\Controller\Adminhtml\AbstractMassStatus//\Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'id';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Swissup\Askit\Model\ResourceModel\Item\Collection';

    /**
     * Page model
     *
     * @var string
     */
    protected $model = 'Swissup\Askit\Model\Item';

    /**
     * item status
     * @var int
     */
    protected $_status = ItemInterface::STATUS_PENDING;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        // \Zend_Debug::dump(__METHOD__);
        // die;
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');

        $this->_status = (int) $this->getRequest()->getParam('change_status', false);
        try {
            if (isset($excluded)) {
                if (!empty($excluded) && 'false' != $excluded) {
                    $this->excludedChange($excluded);
                } else {
                    $this->changeAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedChange($selected);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        // \Zend_Debug::dump($this->_redirect->getRefererUrl());
        // die;
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     *
     * @return void
     * @throws \Exception
     */
    protected function changeAll()
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $this->setSuccessMessage($this->change($collection));
    }

    /**
     *
     * @param array $excluded
     * @return void
     * @throws \Exception
     */
    protected function excludedChange(array $excluded)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setSuccessMessage($this->change($collection));
    }

    /**
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedChange(array $selected)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setSuccessMessage($this->change($collection));
    }

    /**
     *
     * @param AbstractCollection $collection
     * @return int
     */
    protected function change(AbstractCollection $collection)
    {
        $count = 0;
        foreach ($collection->getAllIds() as $id) {
            /** @var \Magento\Framework\Model\AbstractModel $model */
            $model = $this->_objectManager->get($this->model);
            $model->load($id);
            $model->setStatus($this->_status);
            $model->save();
            ++$count;
        }

        return $count;
    }

    /**
     * Set error messages
     *
     * @param int $count
     * @return void
     */
    protected function setSuccessMessage($count)
    {
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been changed.', $count));
    }
}
