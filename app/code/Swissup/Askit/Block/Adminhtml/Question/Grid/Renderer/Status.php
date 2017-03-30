<?php
namespace Swissup\Askit\Block\Adminhtml\Question\Grid\Renderer;

use Swissup\Askit\Api\Data\ItemInterface;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var  \Swissup\Askit\Model\Item\Factory
     */
    protected $modelFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Swissup\Askit\Model\Item\Factory $modelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Swissup\Askit\Model\Item\Factory $modelFactory,
        array $data = []
    ) {
        $this->modelFactory = $modelFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render statusses
     *
     * @return \Magento\Framework\Phrase
     */
    public function getStatus()
    {
        $statuses = $this->modelFactory->create()->getQuestionStatuses();
        if (isset($statuses[$row->getStatus()])) {
            return $statuses[$row->getStatus()];
        }
        return 'None';
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = '';
        $value = $this->getStatus();

        switch ($row->getStatus()) {

            case ItemInterface::STATUS_DISAPPROVED:
                $class = 'critical';
                break;
            case ItemInterface::STATUS_APPROVED:
                $class = 'notice';
                break;
            case ItemInterface::STATUS_DISAPPROVED:
                $class = 'minor';
                break;
            case ItemInterface::STATUS_PENDING:
            case ItemInterface::STATUS_CLOSE:
            default:
                $class = 'minor';
                break;
        }
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
