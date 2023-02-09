<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory as CatalogCategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree as TreeResource;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Helper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\ResourceModel\Category\Tree as BlogTreeResource;

/**
 * @method Tree setUseAjax($useAjax)
 * @method bool getUseAjax()
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    /**
     * @var int Store filter frontend
     */
    protected $_blogStore;

    /**
     * Tree constructor.
     *
     * @param Context $context
     * @param TreeResource $categoryTree
     * @param Registry $registry
     * @param CatalogCategoryFactory $categoryFactory
     * @param EncoderInterface $jsonEncoder
     * @param Helper $resourceHelper
     * @param Session $backendSession
     * @param BlogTreeResource $blogCategoryTree
     * @param CategoryFactory $blogCategoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        TreeResource $categoryTree,
        Registry $registry,
        CatalogCategoryFactory $categoryFactory,
        EncoderInterface $jsonEncoder,
        Helper $resourceHelper,
        Session $backendSession,
        BlogTreeResource $blogCategoryTree,
        CategoryFactory $blogCategoryFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data
        );

        $this->_categoryTree = $blogCategoryTree;
        $this->_categoryFactory = $blogCategoryFactory;
        $this->_withProductCount = false;
    }

    /**
     * @return string
     */
    public function getNodesUrl()
    {
        return $this->getUrl('mageplaza_blog/category/jsonTree');
    }

    /**
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('mageplaza_blog/category/move');
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false];
        $params = array_merge($params, $args);

        return $this->getUrl('mageplaza_blog/*/save', $params);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'mageplaza_blog/category/edit',
            ['store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * @param null $parentNodeCategory
     * @param null $store
     *
     * @return array
     */
    public function getTree($parentNodeCategory = null, $store = null)
    {
        $this->_blogStore = $store;

        return parent::getTree($parentNodeCategory);
    }

    /**
     * Get category name
     *
     * @param DataObject $node
     *
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());

        if ($this->_withProductCount) {
            $result .= ' (' . $node->getProductCount() . ')';
        }

        return $result;
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'category_id', new \Magento\Framework\Data\Tree());
        }

        $storeIds = $node->getStoreIds() ? explode(',', $node->getStoreIds() ?? '') : [];
        if (!($this->_blogStore === null)
            && !empty($storeIds)
            && !in_array(0, $storeIds, false)
            && !in_array($this->_blogStore, $storeIds, false)) {
            return null;
        }

        $node->setIsActive(true);

        if ($item = parent::_getNodeJson($node, $level)) {
            $item['url'] = $node->getData('url_key');
            $item['storeIds'] = $node->getData('store_ids');
            $item['allowDrag'] = $this->_isCategoryMoveable($node) && ($node->getLevel() == 0 ? false : true);
            $item['enabled'] = $node->getData('enabled');

            return $item;
        }

        return null;
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [Category::TREE_ROOT_ID];
            $this->setData('root_ids', $ids);
        }

        return $ids;
    }
}
