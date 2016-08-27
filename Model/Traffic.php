<?php

namespace Mageplaza\Blog\Model;

use Magento\Framework\Model\AbstractModel;

class Traffic extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\Blog\Model\ResourceModel\Traffic');
    }
}
