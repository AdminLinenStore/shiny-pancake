<?php

namespace Swissup\Askit\Block\Question;

use Swissup\Askit\Block\Question\AbstractForm;
use Swissup\Askit\Api\Data\ItemInterface;

class Form extends AbstractForm
{
    protected $formId = 'swissup_askit_new_question_form';

    public function isShow()
    {
        $isLoggedIn = $this->isCustomerLoggedIn();
        $isAllowedGuestQuestion = $this->getConfigHelper()
            ->isAllowedGuestQuestion();

        if (!$isAllowedGuestQuestion && !$isLoggedIn) {
            return false;
        }

        $type = $this->getItemTypeId();
        $types = [ItemInterface::TYPE_CATALOG_PRODUCT,
            ItemInterface::TYPE_CATALOG_CATEGORY,
            ItemInterface::TYPE_CMS_PAGE
        ];
        if (!in_array($type, $types)) {
            return false;
        }

        return true;
    }
}
