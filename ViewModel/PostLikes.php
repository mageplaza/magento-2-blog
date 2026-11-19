<?php

declare(strict_types=1);

namespace Mageplaza\Blog\ViewModel;

use Exception;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Mageplaza\Blog\Model\PostLikeFactory;
use Psr\Log\LoggerInterface;

class PostLikes implements ArgumentInterface
{
    private array $likeCounts = [];

    public function __construct(
        private readonly PostLikeFactory $postLikeFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getLikeCount(int $postId): int
    {
        if (!array_key_exists($postId, $this->likeCounts)) {
            $this->getLikeCounts([$postId]);
        }

        return $this->likeCounts[$postId] ?? 0;
    }

    public function getLikeCounts(array $postIds): array
    {
        $postIds = array_values(array_unique(array_map('intval', $postIds)));
        $missing = array_values(array_diff($postIds, array_keys($this->likeCounts)));

        if ($missing) {
            foreach ($missing as $postId) {
                $this->likeCounts[$postId] = 0;
            }

            try {
                $collection = $this->postLikeFactory->create()->getCollection()
                    ->addFieldToFilter('post_id', ['in' => $missing])
                    ->addFieldToFilter('action', '1');
                $collection->getSelect()
                    ->reset(Select::COLUMNS)
                    ->columns(['post_id', 'total' => new Expression('COUNT(*)')])
                    ->group('post_id');
                foreach ($collection->getData() as $row) {
                    $this->likeCounts[(int) $row['post_id']] = (int) $row['total'];
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return array_intersect_key($this->likeCounts, array_flip($postIds));
    }
}
