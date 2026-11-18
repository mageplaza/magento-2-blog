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

declare(strict_types=1);

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Mageplaza\Blog\Model\Ai\Service;

class AiAssist extends Template implements RendererInterface
{
    public const AI_CONFIG_SECTION = 'adminhtml/system_config/edit/section/mp_ai_integration';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Blog::post/ai_assist.phtml';

    /**
     * @var Service
     */
    private $service;

    /**
     * @param Context $context
     * @param Service $service
     * @param array $data
     */
    public function __construct(Context $context, Service $service, array $data = [])
    {
        $this->service = $service;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->_authorization->isAllowed('Mageplaza_Blog::ai_assist') ? parent::toHtml() : '';
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->service->isAvailable();
    }

    /**
     * @return string
     */
    public function getConfigUrl(): string
    {
        return $this->getUrl(self::AI_CONFIG_SECTION);
    }

    /**
     * @return string
     */
    public function getJsConfig(): string
    {
        return (string)json_encode([
            'Mageplaza_Blog/js/ai/assist' => [
                'url' => $this->getUrl('mageplaza_blog/ai/assist'),
                'createUrl' => $this->getUrl('mageplaza_blog/ai/createTerm'),
                'canCreate' => [
                    'tags' => $this->_authorization->isAllowed('Mageplaza_Blog::tag'),
                    'topics' => $this->_authorization->isAllowed('Mageplaza_Blog::topic')
                ]
            ]
        ]);
    }
}
