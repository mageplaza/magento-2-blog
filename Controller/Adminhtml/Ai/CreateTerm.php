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

namespace Mageplaza\Blog\Controller\Adminhtml\Ai;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\Ai\Service;
use Mageplaza\Blog\Model\Ai\TermCreator;

class CreateTerm extends AbstractAi
{
    public const ADMIN_RESOURCE = 'Mageplaza_Blog::ai_assist';

    /**
     * @var TermCreator
     */
    private $termCreator;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Service $service
     * @param TermCreator $termCreator
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Service $service,
        TermCreator $termCreator
    ) {
        $this->termCreator = $termCreator;

        parent::__construct($context, $resultJsonFactory, $service);
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws LocalizedException
     */
    protected function process(RequestInterface $request): array
    {
        $type = (string)$request->getParam('type');
        $resource = $type === TermCreator::TYPE_TOPIC ? 'Mageplaza_Blog::topic' : 'Mageplaza_Blog::tag';
        if (!$this->_authorization->isAllowed($resource)) {
            throw new LocalizedException(__('You are not allowed to use this feature.'));
        }

        return $this->termCreator->createOrFind($type, (string)$request->getParam('name'));
    }
}
