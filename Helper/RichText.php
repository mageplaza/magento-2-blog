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
 * Sanitizes rich text before it is rendered on the storefront
 */
class RichText
{
    public const INLINE_TAGS = ['p', 'br', 'b', 'i', 'strong', 'em', 'a', 'ul', 'ol', 'li', 'span'];

    public const CONTENT_TAGS = [
        'p', 'br', 'b', 'i', 'u', 's', 'strong', 'em', 'a', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'hr', 'img',
        'figure', 'figcaption', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'span', 'div', 'sub', 'sup', 'iframe', 'video', 'audio', 'source'
    ];

    public const VOID_TAGS = ['br', 'hr', 'img', 'source'];

    public const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template',
        'svg', 'math', 'head', 'title', 'applet', 'frame', 'frameset', 'select', 'textarea'
    ];

    public const CONTENT_ATTRIBUTES = [
        'a'      => ['href', 'title', 'target', 'rel'],
        'img'    => ['src', 'alt', 'width', 'height', 'loading', 'srcset', 'sizes', 'data-src', 'style', 'align'],
        'table'  => ['style', 'align'],
        'td'     => ['colspan', 'rowspan', 'style', 'align'],
        'th'     => ['colspan', 'rowspan', 'style', 'align'],
        'p'      => ['style', 'align'],
        'h1'     => ['style'],
        'h2'     => ['style'],
        'h3'     => ['style'],
        'h4'     => ['style'],
        'h5'     => ['style'],
        'h6'     => ['style'],
        'div'    => ['style'],
        'span'   => ['style'],
        'figure' => ['style'],
        'iframe' => ['src', 'width', 'height', 'title', 'allowfullscreen', 'frameborder'],
        'video'  => ['src', 'poster', 'width', 'height', 'controls', 'autoplay', 'muted', 'loop', 'playsinline', 'preload'],
        'audio'  => ['src', 'controls', 'autoplay', 'muted', 'loop', 'preload'],
        'source' => ['src', 'type']
    ];

    public const INLINE_ATTRIBUTES = [
        'a' => ['href', 'title']
    ];

    public const IFRAME_HOSTS = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com'];

    public const LINK_SCHEMES = ['http', 'https', 'mailto'];

    public const MEDIA_SCHEMES = ['http', 'https'];

    public const LINK_TARGETS = ['_blank', '_self', '_parent', '_top'];

    public const LOADING_VALUES = ['lazy', 'eager'];

    public const PRELOAD_VALUES = ['none', 'metadata', 'auto'];

    public const ALIGN_VALUES = ['left', 'right', 'center', 'justify'];

    public const STYLE_PROPERTIES = [
        'width', 'height', 'max-width', 'text-align', 'vertical-align', 'font-weight', 'font-style',
        'text-decoration', 'color', 'background-color'
    ];

    public const STYLE_PROPERTY_PREFIXES = ['padding', 'margin', 'border'];

    /**
     * Sanitize rich text that only allows basic inline markup
     *
     * @param string $html
     *
     * @return string
     */
    public function sanitizeInline(string $html): string
    {
        return $this->sanitize($html, self::INLINE_TAGS, self::INLINE_ATTRIBUTES, false);
    }

    /**
     * Sanitize full post content
     *
     * @param string $html
     *
     * @return string
     */
    public function sanitizeContent(string $html): string
    {
        return $this->sanitize($html, self::CONTENT_TAGS, self::CONTENT_ATTRIBUTES, true);
    }

    /**
     * Parse the markup and render only the allowed tags and attributes
     *
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
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
            . '</head><body>' . $html . '</body></html>'
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
     * Render the child nodes of a node
     *
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
     * Render one element when its tag is allowed
     *
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
     * Render the allowed attributes of an element
     *
     * @param DOMElement $element
     * @param string $name
     * @param array $attributes
     * @param bool $globalAttributes
     *
     * @return string
     */
    private function renderAttributes(
        DOMElement $element,
        string $name,
        array $attributes,
        bool $globalAttributes
    ): string {
        $allowed = $attributes[$name] ?? [];
        if ($globalAttributes) {
            $allowed = array_merge($allowed, ['class', 'id']);
        }

        $clean = [];
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $attrName = strtolower($attribute->nodeName);
            if (!in_array($attrName, $allowed, true) && !($globalAttributes && $this->isAriaAttribute($attrName))) {
                continue;
            }
            $value = $this->cleanAttribute($name, $attrName, (string) $attribute->nodeValue);
            if ($value !== null) {
                $clean[$attrName] = $value;
            }
        }

        if ($name === 'a' && ($clean['target'] ?? '') === '_blank') {
            $clean['rel'] = $this->withNoopener($clean['rel'] ?? '');
        }

        $out = '';
        foreach ($clean as $attrName => $value) {
            $out .= ' ' . $attrName . '="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }

        return $out;
    }

    /**
     * Validate and normalize one attribute value, null means drop it
     *
     * @param string $tag
     * @param string $attribute
     * @param string $value
     *
     * @return string|null
     */
    private function cleanAttribute(string $tag, string $attribute, string $value): ?string
    {
        if ($this->isAriaAttribute($attribute)) {
            return preg_match('/[<>]/', $value) ? null : $value;
        }

        switch ($attribute) {
            case 'href':
                return $this->isSafeUrl($value, self::LINK_SCHEMES) ? $value : null;
            case 'src':
                if ($tag === 'iframe') {
                    return $this->isAllowedIframeSource($value) ? $value : null;
                }

                return $this->isSafeUrl($value, self::MEDIA_SCHEMES) ? $value : null;
            case 'data-src':
            case 'poster':
                return $this->isSafeUrl($value, self::MEDIA_SCHEMES) ? $value : null;
            case 'srcset':
                return $this->cleanSrcset($value);
            case 'sizes':
                return preg_match('/^[\w\s,.:()%+*\/\-]+$/', $value) ? trim($value) : null;
            case 'loading':
                return in_array(strtolower(trim($value)), self::LOADING_VALUES, true) ? strtolower(trim($value)) : null;
            case 'target':
                return in_array(strtolower(trim($value)), self::LINK_TARGETS, true) ? strtolower(trim($value)) : null;
            case 'rel':
                $tokens = preg_split('/\s+/', strtolower(trim($value)), -1, PREG_SPLIT_NO_EMPTY);
                $tokens = array_filter($tokens, static function ($token) {
                    return preg_match('/^[a-z]+$/', $token);
                });

                return $tokens ? implode(' ', $tokens) : null;
            case 'align':
                return in_array(strtolower(trim($value)), self::ALIGN_VALUES, true) ? strtolower(trim($value)) : null;
            case 'style':
                return $this->cleanStyle($value);
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
            case 'controls':
            case 'autoplay':
            case 'muted':
            case 'loop':
            case 'playsinline':
                return '';
            case 'preload':
                return in_array(strtolower(trim($value)), self::PRELOAD_VALUES, true) ? strtolower(trim($value)) : null;
            case 'type':
                return preg_match('/^[a-z]+\/[\w.+\-]+$/i', trim($value)) ? trim($value) : null;
            case 'allowfullscreen':
            case 'frameborder':
                return $value === '' ? 'true' : preg_replace('/[^\w\-]/', '', $value);
        }

        return $value;
    }

    /**
     * Check whether the attribute name is aria-*
     *
     * @param string $attribute
     *
     * @return bool
     */
    private function isAriaAttribute(string $attribute): bool
    {
        return (bool) preg_match('/^aria-[a-z]+$/', $attribute);
    }

    /**
     * Make sure a rel value carries noopener and no opener
     *
     * @param string $rel
     *
     * @return string
     */
    private function withNoopener(string $rel): string
    {
        $tokens = array_diff(preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY), ['opener']);
        if (!in_array('noopener', $tokens, true)) {
            $tokens[] = 'noopener';
        }

        return implode(' ', $tokens);
    }

    /**
     * Keep a srcset only when every candidate URL is safe
     *
     * @param string $value
     *
     * @return string|null
     */
    private function cleanSrcset(string $value): ?string
    {
        $candidates = [];
        foreach (explode(',', $value) as $candidate) {
            $parts = preg_split('/\s+/', trim($candidate), -1, PREG_SPLIT_NO_EMPTY);
            if (!$parts || count($parts) > 2 || !$this->isSafeUrl($parts[0], self::MEDIA_SCHEMES)) {
                return null;
            }
            if (isset($parts[1]) && !preg_match('/^\d+(\.\d+)?[wx]$/', $parts[1])) {
                return null;
            }
            $candidates[] = implode(' ', $parts);
        }

        return $candidates ? implode(', ', $candidates) : null;
    }

    /**
     * Keep only allowlisted CSS declarations with safe values
     *
     * @param string $value
     *
     * @return string|null
     */
    private function cleanStyle(string $value): ?string
    {
        $declarations = [];
        foreach (explode(';', $value) as $declaration) {
            $pair = explode(':', $declaration, 2);
            if (count($pair) !== 2) {
                continue;
            }
            $property = strtolower(trim($pair[0]));
            $cssValue = trim($pair[1]);
            if ($cssValue === '' || !$this->isAllowedStyleProperty($property)) {
                continue;
            }
            if (preg_match('/url\s*\(|expression|javascript|@import|[\\\\<>]/i', $cssValue)
                || !preg_match('/^[\w\s#%.,()+!\-]+$/', $cssValue)
            ) {
                continue;
            }
            $declarations[] = $property . ':' . $cssValue;
        }

        return $declarations ? implode(';', $declarations) : null;
    }

    /**
     * Check whether a CSS property is allowlisted
     *
     * @param string $property
     *
     * @return bool
     */
    private function isAllowedStyleProperty(string $property): bool
    {
        if (in_array($property, self::STYLE_PROPERTIES, true)) {
            return true;
        }
        foreach (self::STYLE_PROPERTY_PREFIXES as $prefix) {
            if ($property === $prefix || strpos($property, $prefix . '-') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strip entities and control characters so scheme checks see the real URL
     *
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
     * Check that a URL is relative or uses an allowed scheme
     *
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
     * Check that an iframe source points to an allowlisted host
     *
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
