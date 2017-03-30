<?php
namespace Swissup\Reviewreminder\Controller\Adminhtml\Index;

use Swissup\Reviewreminder\Api\Data\EntityInterface;
use Magento\Framework\Controller\ResultFactory;
use Swissup\Reviewreminder\Model\ResourceModel\Entity\Collection as AbstractCollection;

/**
 * Class MassStatus
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'entity_id';
    /**
     * Redirect url
     */
    const REDIRECT_URL = '*/*/';
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_Reviewreminder::status';
    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Swissup\Reviewreminder\Model\ResourceModel\Entity\Collection';
    /**
     * Entity model
     *
     * @var string
     */
    protected $model = 'Swissup\Reviewreminder\Model\Entity';
    /**
     * entity status
     * @var int
     */
    protected $_status = \Swissup\Reviewreminder\Model\Entity::STATUS_PENDING;

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');

        $this->_status = (int) $this->getRequest()->getParam('status');
        try {
            if (isset($excluded)) {
                if (!empty($excluded) && $excluded != "false") {
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

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL);
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
