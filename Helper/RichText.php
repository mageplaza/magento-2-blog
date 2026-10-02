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

namespace Mageplaza\Blog\Helper;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Class RichText
 * @package Mageplaza\Blog\Helper
 */
class RichText
{
    const INLINE_TAGS = ['p', 'br', 'b', 'i', 'strong', 'em', 'a', 'ul', 'ol', 'li', 'span'];

    const CONTENT_TAGS = [
        'p', 'br', 'b', 'i', 'u', 's', 'strong', 'em', 'a', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'hr', 'img',
        'figure', 'figcaption', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'span', 'div', 'sub', 'sup', 'iframe'
    ];

    const VOID_TAGS = ['br', 'hr', 'img'];

    const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template',
        'svg', 'math', 'head', 'title', 'applet', 'frame', 'frameset', 'select', 'textarea'
    ];

    const CONTENT_ATTRIBUTES = [
        'a'      => ['href', 'title'],
        'img'    => ['src', 'alt', 'width', 'height'],
        'td'     => ['colspan', 'rowspan'],
        'th'     => ['colspan', 'rowspan'],
        'iframe' => ['src', 'width', 'height', 'title', 'allowfullscreen', 'frameborder']
    ];

    const INLINE_ATTRIBUTES = [
        'a' => ['href', 'title']
    ];

    const IFRAME_HOSTS = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com'];

    const LINK_SCHEMES = ['http', 'https', 'mailto'];

    const MEDIA_SCHEMES = ['http', 'https'];

    /**
     * @param string $html
     *
     * @return string
     */
    public function sanitizeInline(string $html): string
    {
        return $this->sanitize($html, self::INLINE_TAGS, self::INLINE_ATTRIBUTES, false);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function sanitizeContent(string $html): string
    {
        return $this->sanitize($html, self::CONTENT_TAGS, self::CONTENT_ATTRIBUTES, true);
    }

    /**
     * @param string $html
     * @param array $tags
     * @param array $attributes
     * @param bool $globalAttributes
     *
     * @return string
     */
    private function sanitize(string $html, array $tags, array $attributes, bool $globalAttributes): string
    {
        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom      = new DOMDocument();
        $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'
            . $html . '</body></html>'
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        return $this->renderChildren($body, $tags, $attributes, $globalAttributes);
    }

    /**
     * @param DOMNode $node
     * @param array $tags
     * @param array $attributes
     * @param bool $globalAttributes
     *
     * @return string
     */
    private function renderChildren(DOMNode $node, array $tags, array $attributes, bool $globalAttributes): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $out .= htmlspecialchars($child->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            } elseif ($child instanceof DOMElement) {
                $out .= $this->renderElement($child, $tags, $attributes, $globalAttributes);
            }
        }

        return $out;
    }

    /**
     * @param DOMElement $element
     * @param array $tags
     * @param array $attributes
     * @param bool $globalAttributes
     *
     * @return string
     */
    private function renderElement(DOMElement $element, array $tags, array $attributes, bool $globalAttributes): string
    {
        $name = strtolower($element->nodeName);

        if ($name === 'iframe' && in_array($name, $tags, true)) {
            $attrs = $this->renderAttributes($element, $name, $attributes, $globalAttributes);

            return strpos($attrs, ' src="') !== false ? '<iframe' . $attrs . '></iframe>' : '';
        }

        if (in_array($name, self::DROP_WITH_CONTENT, true)) {
            return '';
        }

        $inner = $this->renderChildren($element, $tags, $attributes, $globalAttributes);
        if (!in_array($name, $tags, true)) {
            return $inner;
        }

        $attrs = $this->renderAttributes($element, $name, $attributes, $globalAttributes);
        if ($name === 'img' && strpos($attrs, ' src="') === false) {
            return '';
        }
        if (in_array($name, self::VOID_TAGS, true)) {
            return '<' . $name . $attrs . '>';
        }

        return '<' . $name . $attrs . '>' . $inner . '</' . $name . '>';
    }

    /**
     * @param DOMElement $element
     * @param string $name
     * @param array $attributes
     * @param bool $globalAttributes
     *
     * @return string
     */
    private function renderAttributes(DOMElement $element, string $name, array $attributes, bool $globalAttributes): string
    {
        $allowed = $attributes[$name] ?? [];
        if ($globalAttributes) {
            $allowed = array_merge($allowed, ['class', 'id']);
        }

        $out = '';
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $attrName = strtolower($attribute->nodeName);
            if (!in_array($attrName, $allowed, true)) {
                continue;
            }
            $value = $this->cleanAttribute($name, $attrName, (string) $attribute->nodeValue);
            if ($value === null) {
                continue;
            }
            $out .= ' ' . $attrName . '="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }

        return $out;
    }

    /**
     * @param string $tag
     * @param string $attribute
     * @param string $value
     *
     * @return string|null
     */
    private function cleanAttribute(string $tag, string $attribute, string $value): ?string
    {
        switch ($attribute) {
            case 'href':
                return $this->isSafeUrl($value, self::LINK_SCHEMES) ? $value : null;
            case 'src':
                if ($tag === 'iframe') {
                    return $this->isAllowedIframeSource($value) ? $value : null;
                }

                return $this->isSafeUrl($value, self::MEDIA_SCHEMES) ? $value : null;
            case 'width':
            case 'height':
                return preg_match('/^\d{1,5}(px|%)?$/', trim($value)) ? trim($value) : null;
            case 'colspan':
            case 'rowspan':
                return preg_match('/^\d{1,3}$/', trim($value)) ? trim($value) : null;
            case 'class':
            case 'id':
                $value = trim((string) preg_replace('/[^\w\- ]/u', '', $value));

                return $value === '' ? null : $value;
            case 'allowfullscreen':
            case 'frameborder':
                return $value === '' ? 'true' : preg_replace('/[^\w\-]/', '', $value);
        }

        return $value;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function normalizeUrl(string $value): string
    {
        for ($i = 0; $i < 3; $i++) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
        }

        return (string) preg_replace('/[\x00-\x20\x7f\x{00a0}\x{2028}\x{2029}]+/u', '', $value);
    }

    /**
     * @param string $value
     * @param array $schemes
     *
     * @return bool
     */
    private function isSafeUrl(string $value, array $schemes): bool
    {
        $url = $this->normalizeUrl($value);
        if ($url === '') {
            return true;
        }
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $url, $match)) {
            return in_array(strtolower($match[1]), $schemes, true);
        }

        return true;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isAllowedIframeSource(string $value): bool
    {
        $url = $this->normalizeUrl($value);
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }
        if (!preg_match('#^https?://#i', $url)) {
            return false;
        }
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && in_array(strtolower($host), self::IFRAME_HOSTS, true);
    }
}
