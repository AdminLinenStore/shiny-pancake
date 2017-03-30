<?php
namespace Swissup\Askit\Controller\Adminhtml\Item;

use Swissup\Askit\Controller\Adminhtml\AbstractMassStatus;

/**
 * Class MassEnable
 */
class MassDisable extends AbstractMassStatus
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
     * item model
     *
     * @var string
     */
    protected $model = 'Swissup\Askit\Model\Item';

    /**
     * item enable status
     *
     * @var boolean
     */
    protected $status = false;
}