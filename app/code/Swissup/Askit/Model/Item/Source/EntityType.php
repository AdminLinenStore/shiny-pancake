<?php
namespace Swissup\Askit\Model\Item\Source;

class EntityType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Swissup\Askit\Model\Item
     */
    protected $item;

    /**
     * Constructor
     *
     * @param \Swissup\Askit\Model\Item $item
     */
    public function __construct(\Swissup\Askit\Model\Item $item)
    {
        $this->item = $item;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->item->getEntityTypes();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}