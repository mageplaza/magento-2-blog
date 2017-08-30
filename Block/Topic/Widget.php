<?php
/**
 * Created by PhpStorm.
 * User: HoangKuty
 * Date: 4/7/2017
 * Time: 9:15 AM
 */
namespace Mageplaza\Blog\Block\Topic;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Topic
 */
class Widget extends Frontend
{

	/**
	 * @return array|string
	 */
    public function getTopicList()
    {
        return $this->helperData->getTopicList();
    }

	/**
	 * @param $topic
	 * @return string
	 */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getTopicUrl($topic);
    }
}
