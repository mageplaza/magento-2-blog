<?php

namespace Mageplaza\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Traffic extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_post_traffic', 'traffic_id');
    }
}
