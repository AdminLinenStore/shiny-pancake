<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Api;

use Magento\Catalog\Model\Category;

interface CategoryDataSetterInterface
{
    public function setCategoryData(Category $category);
}