<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\Downloads\Block;

use MageWorx\Downloads\Model\Attachment;

class Links extends \MageWorx\Downloads\Block\AttachmentContainer
{
    /**
     * Prepare URL rewrite editing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('attachment_container.phtml');

        $title = trim($this->getTitle());
        if (empty($title)) {
            $this->setTitle($this->helperData->getFileDownloadsTitle());
        }

        $id = $this->getId();
        if (empty($id) && $this->getIds()) {
            $id = implode(',', $this->getIds());
        }

        if (empty($id)) {
            return '';
        }

        $ids = explode(',', $id);
        $attachmentCollection = $this->attachmentCollectionFactory->create();
        $attachment = $this->attachmentFactory->create();
        $attachmentCollection
            ->addFieldToFilter($attachment->getIdFieldName(), $ids)
            ->addSortOrder()
            ->addFieldToFilter('is_active', Attachment::STATUS_ENABLED)
            ->addFieldToFilter('section_table.is_active', \MageWorx\Downloads\Model\Section::STATUS_ENABLED);

        $attachmentCollection->addCustomerGroupFilter($this->getCustomerGroupId());

        $items = $attachmentCollection->getItems();
        $inGroupIds = $attachmentCollection->getAllIds();
        foreach ($items as $item) {
            if (!$this->isAllowByCount($item)) {
                continue;
            }

            if ($this->isAllowByCustomerGroup($item, $inGroupIds)) {
                $item->setIsInGroup('1');
            } else {
                $this->isHasNotAllowedLinks = true;
            }
                $this->attachments[] = $item;
        }
        return parent::_prepareLayout();
    }

    /**
     *
     * @return array
     */
    public function getAttachments()
    {
        if (!$this->attachments) {
            $collection = $this->attachmentCollectionFactory->create();
            $inGroupIds = $collection->getAllIds();

            foreach ($collection->getItems() as $item) {
                if (!$this->isAllowByCount($item)) {
                    continue;
                }

                if ($this->isAllowByCustomerGroup($item, $inGroupIds)) {
                    $item->setIsInGroup('1');
                } else {
                    $this->isHasNotAllowedLinks = true;
                }

                $this->attachments[] = $item;
            }
        }

        return $this->attachments;
    }
}
