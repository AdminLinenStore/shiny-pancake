<?php
namespace Swissup\Askit\Model\Item;

use Magento\Framework\Data\OptionSourceInterface;
use Swissup\Askit\Api\Data\ItemInterface;

class Status implements OptionSourceInterface
{
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            ItemInterface::STATUS_PENDING     => __('Pending'),
            ItemInterface::STATUS_APPROVED    => __('Approved'),
            ItemInterface::STATUS_DISAPPROVED => __('Disapproved'),
            ItemInterface::STATUS_CLOSE       => __('Close')
        ];
    }

    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
