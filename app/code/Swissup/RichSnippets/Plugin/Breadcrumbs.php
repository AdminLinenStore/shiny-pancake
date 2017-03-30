<?php
namespace Swissup\RichSnippets\Plugin;

use Magento\Theme\Block\Html\Breadcrumbs as Subject;

class Breadcrumbs
{
    /**
     * List of breadcrumbs
     *
     * @var array
     */
    public $crumbs;

    public function beforeAddCrumb(Subject $subject, $crumbName, $crumbInfo)
    {
        if (!isset($this->crumbs[$crumbName])) {
            $this->crumbs[$crumbName] = $crumbInfo;
        }
        return;
    }
}
