<?php
namespace Swissup\Attributepages\Block\Adminhtml\Page\Edit\Tab\Renderer;

use \Magento\Framework\DataObject;
use \Swissup\Attributepages\Model\Entity as AttributepagesEntity;
use \Magento\Framework\UrlInterface;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_type = 'image';

    public function render(DataObject $row)
    {
        $output = '<div style="float: left; width: 25px; height: 25px;">';
        if ($image = $row->getData($this->_type)) {
            $output .= '<img src="' . $this->getBaseUrl()
                . AttributepagesEntity::IMAGE_PATH
                . $image
                . '"'
                . 'alt="' . $row->getIdentifier() . '" width="25" height="25"/>';
        }
        $output .= '</div>';
        $optionId = $row->getOptionId();
        $output .= '<input type="file" style="width: 180px;" name="option_' . $optionId . '_' . $this->_type . '"/>';
        $output .= '<input type="hidden" value="' . $row->getEntityId() . '" name="option['. $optionId .'][entity_id]"/>';
        return $output;
    }
    /**
     * Get images base url
     *
     * @return string
     */
    public function getBaseUrl($type = UrlInterface::URL_TYPE_MEDIA)
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => $type]);
    }
}
