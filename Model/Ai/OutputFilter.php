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

use DOMDocument;
use DOMNode;

class OutputFilter
{
    private const ALLOWED_TAGS = [
        'h2', 'h3', 'p', 'ul', 'ol', 'li', 'strong', 'em', 'a',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'blockquote'
    ];

    private const REMOVED_TAGS = ['script', 'style'];

    /**
     * @param string $html
     * @return string
     */
    public function filterHtml(string $html): string
    {
        $html = preg_replace('/^[ \t]*```[a-zA-Z0-9_-]*[ \t]*$/m', '', $html);
        $html = trim($this->removeDirectives($html));
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><html><body><div id="mp-ai-root">' . $html . '</div></body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('mp-ai-root');

        return $root ? trim($this->removeDirectives($this->render($root))) : '';
    }

    /**
     * @param string $text
     * @return string
     */
    public function filterText(string $text): string
    {
        return trim($this->removeDirectives(strip_tags($text)));
    }

    /**
     * @param string $value
     * @return string
     */
    private function removeDirectives(string $value): string
    {
        do {
            $previous = $value;
            $value = preg_replace('/\{\{.*?\}\}/s', '', $value);
            $value = str_replace('{{', '', $value);
        } while ($value !== $previous);

        return $value;
    }

    /**
     * @param DOMNode $node
     * @return string
     */
    private function render(DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $out .= htmlspecialchars($child->nodeValue, ENT_NOQUOTES, 'UTF-8');
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::REMOVED_TAGS, true)) {
                continue;
            }
            $inner = $this->render($child);
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                $out .= $inner;
                continue;
            }
            if ($tag === 'a') {
                $href = $this->safeHref((string)$child->getAttribute('href'));
                $out .= $href === ''
                    ? $inner
                    : '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . $inner . '</a>';
                continue;
            }
            $out .= '<' . $tag . '>' . $inner . '</' . $tag . '>';
        }

        return $out;
    }

    /**
     * @param string $href
     * @return string
     */
    private function safeHref(string $href): string
    {
        $href = trim(preg_replace('/[\x00-\x20\x7f]+/', '', $href));
        if ($href === '' || strpos($href, '//') === 0) {
            return '';
        }
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $href)) {
            return preg_match('#^https?://#i', $href) ? $href : '';
        }

        return $href;
    }
}
