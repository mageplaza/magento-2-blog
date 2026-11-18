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

namespace Mageplaza\Blog\Model\Ai;

class TermFilter
{
    private const MAX_LENGTH = 80;

    private const MAX_WORDS = 8;

    private const MIN_DIGIT_RUN = 7;

    private const ATTACK_PATTERN = '/\bunion\s+(all\s+)?select\b|\bselect\s.+\sfrom\b|<\s*script|[\'"]\s*or\s+[\'"\d]|\bdrop\s+table\b/i';

    /**
     * @param array $rows
     * @return array
     */
    public function filter(array $rows): array
    {
        $kept = [];
        $removed = 0;

        foreach ($rows as $row) {
            $text = trim((string)($row['query_text'] ?? ''));
            if ($text === '' || !$this->isSafe($text)) {
                $removed++;
                continue;
            }

            $key = mb_strtolower($text, 'UTF-8');
            $popularity = (int)($row['popularity'] ?? 0);
            if (isset($kept[$key])) {
                $kept[$key]['popularity'] += $popularity;
                continue;
            }

            $kept[$key] = ['query_text' => $text, 'popularity' => $popularity];
        }

        return ['terms' => array_values($kept), 'removed' => $removed];
    }

    /**
     * @param string $text
     * @return bool
     */
    private function isSafe(string $text): bool
    {
        if (preg_match('/@|https?|www\.|\{\{|[<>]|[\p{L}\d-]+\.(com|net|org|vn|io|info|biz)\b/iu', $text)) {
            return false;
        }

        if (preg_match('/^\d{' . self::MIN_DIGIT_RUN . ',}$/', preg_replace('/[\s().+\-]/u', '', $text))) {
            return false;
        }

        if (mb_strlen($text, 'UTF-8') > self::MAX_LENGTH
            || count(preg_split('/\s+/u', $text)) > self::MAX_WORDS
        ) {
            return false;
        }

        return !preg_match(self::ATTACK_PATTERN, $text . ' ');
    }
}
