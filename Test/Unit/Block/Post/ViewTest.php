<?php
/**
 * Unit tests for Mageplaza\Blog\Block\Post\View
 *
 * Strategy: disable original constructor (which requires many generated factory classes),
 * then manually inject only the dependencies needed per test via reflection.
 */

declare(strict_types=1);

namespace Mageplaza\Blog\Test\Unit\Block\Post;

use Magento\Framework\Escaper;
use Mageplaza\Blog\Block\Post\View;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Post;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ViewTest extends TestCase
{
    /** @var View|MockObject */
    private MockObject $block;

    /** @var Escaper|MockObject */
    private MockObject $escaper;

    /** @var Data|MockObject */
    private MockObject $helperData;

    protected function setUp(): void
    {
        // Disable original constructor to avoid resolving all generated factory classes
        $this->block = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->onlyMethods([]) // use real implementation for all methods
            ->getMock();

        // Use real escaper behavior for meaningful security assertions
        $this->escaper = $this->createMock(Escaper::class);
        $this->escaper->method('escapeHtml')->willReturnCallback(
            fn($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false)
        );
        $this->escaper->method('escapeUrl')->willReturnCallback(
            fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
        );

        $this->helperData = $this->createMock(Data::class);

        // Inject _escaper (protected property inherited from AbstractBlock)
        $this->injectProperty('_escaper', $this->escaper);
        // Inject helperData (public property defined in Frontend)
        $this->injectProperty('helperData', $this->helperData);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Inject a property value into the block mock by walking up the class hierarchy.
     */
    private function injectProperty(string $name, mixed $value): void
    {
        $class = new \ReflectionClass($this->block);
        // Walk up parent classes to find where the property is declared
        while ($class) {
            if ($class->hasProperty($name)) {
                $prop = $class->getProperty($name);
                $prop->setValue($this->block, $value);
                return;
            }
            $class = $class->getParentClass();
        }
        throw new \RuntimeException("Property '{$name}' not found in class hierarchy.");
    }

    // -------------------------------------------------------------------------
    // commentHtml()
    // -------------------------------------------------------------------------

    /** @test — single line wrapped in one <p> tag */
    public function testCommentHtmlSingleLine(): void
    {
        $result = $this->block->commentHtml('Hello World');
        $this->assertSame('<p>Hello World</p>', $result);
    }

    /** @test — multiple newlines produce multiple <p> tags */
    public function testCommentHtmlMultipleLines(): void
    {
        $result = $this->block->commentHtml("Line one\nLine two");
        $this->assertSame('<p>Line one</p><p>Line two</p>', $result);
    }

    /** @test — XSS payload must be HTML-escaped */
    public function testCommentHtmlEscapesXss(): void
    {
        $result = $this->block->commentHtml('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    /** @test — leading/trailing whitespace and empty lines are trimmed */
    public function testCommentHtmlTrimmed(): void
    {
        $result = $this->block->commentHtml("  Hello  \n");
        $this->assertSame('<p>Hello</p>', $result);
    }

    /** @test — & character is escaped to &amp; */
    public function testCommentHtmlEscapesAmpersand(): void
    {
        $result = $this->block->commentHtml('a & b');
        $this->assertStringContainsString('&amp;', $result);
    }

    public function testCommentHtmlDoesNotDoubleEncodeExistingEntities(): void
    {
        $result = $this->block->commentHtml('Tom &amp; Jerry');
        $this->assertStringContainsString('Tom &amp; Jerry', $result);
        $this->assertStringNotContainsString('&amp;amp;', $result);
    }

    // -------------------------------------------------------------------------
    // getTagList()
    // -------------------------------------------------------------------------

    /** @test — empty collection returns empty string */
    public function testGetTagListEmptyCollection(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getSelectedTagsCollection')->willReturn([]);

        $this->assertSame('', $this->block->getTagList($post));
    }

    /** @test — single tag renders correct anchor markup */
    public function testGetTagListRendersAnchor(): void
    {
        $tag = $this->createTagMock('PHP', 'https://example.com/blog/tag/php');
        $this->helperData->method('getBlogUrl')->willReturn('https://example.com/blog/tag/php');

        $post = $this->createMock(Post::class);
        $post->method('getSelectedTagsCollection')->willReturn([$tag]);

        $result = $this->block->getTagList($post);
        $this->assertStringContainsString('class="mp-info"', $result);
        $this->assertStringContainsString('href="', $result);
        $this->assertStringContainsString('>PHP<', $result);
    }

    /** @test — multiple tags are joined by ", " */
    public function testGetTagListMultipleTagsJoined(): void
    {
        $this->helperData->method('getBlogUrl')->willReturn('https://example.com/tag/x');

        $post = $this->createMock(Post::class);
        $post->method('getSelectedTagsCollection')->willReturn([
            $this->createTagMock('PHP'),
            $this->createTagMock('Magento'),
        ]);

        $result = $this->block->getTagList($post);
        $parts  = explode(', ', $result);
        $this->assertCount(2, $parts);
    }

    /** @test — tag name with XSS is escaped in output */
    public function testGetTagListEscapesTagName(): void
    {
        $this->helperData->method('getBlogUrl')->willReturn('https://example.com/tag/x');

        $post = $this->createMock(Post::class);
        $post->method('getSelectedTagsCollection')->willReturn([
            $this->createTagMock('<script>xss</script>'),
        ]);

        $result = $this->block->getTagList($post);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testGetCommentsTreeGuestUserNameStaysEscaped(): void
    {
        $this->injectLikeFactory();

        $rawPayload = '<script>alert(1)</script> & "quoted" \'single\'';
        $encodedUserName = htmlspecialchars($rawPayload, ENT_COMPAT, 'UTF-8');

        $comment = $this->makeCommentRow(['user_name' => $encodedUserName]);

        $this->block->getCommentsTree([$comment], 0);
        $result = $this->block->getCommentsHtml();

        $this->assertStringContainsString($encodedUserName, $result);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('</script>', $result);

        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringContainsString('&amp;', $result);
        $this->assertStringContainsString('&quot;quoted&quot;', $result);
    }

    public function testGuestUserNameEncodingLeavesNonAsciiIntact(): void
    {
        $this->injectLikeFactory();

        $vietnameseName = 'Nguyễn Thái Sơn';

        // The encoding actually applied by T-XSS-1 in both controllers.
        $encoded = htmlspecialchars($vietnameseName, ENT_COMPAT, 'UTF-8');

        // Nothing to escape in a plain Vietnamese name — it must pass through
        // byte-for-byte. (htmlentities() would yield 'Nguyễn Th&aacute;i Sơn'.)
        $this->assertSame($vietnameseName, $encoded);
        $this->assertStringNotContainsString('&aacute;', $encoded);

        // And it must still render intact through the comment tree.
        $comment = $this->makeCommentRow(['user_name' => $encoded]);
        $this->block->getCommentsTree([$comment], 0);
        $result = $this->block->getCommentsHtml();

        $this->assertStringContainsString($vietnameseName, $result);
        $this->assertStringNotContainsString('&aacute;', $result);
    }

    public function testGetCommentsTreeGuestUserNameRawWouldBeUnsafe(): void
    {
        $this->injectLikeFactory();

        $rawPayload = '<script>alert(1)</script>';
        $comment    = $this->makeCommentRow(['user_name' => $rawPayload]);

        $this->block->getCommentsTree([$comment], 0);
        $result = $this->block->getCommentsHtml();

        $this->assertStringContainsString('<script>alert(1)</script>', $result);
    }

    // -------------------------------------------------------------------------
    // isLoggedIn()
    // -------------------------------------------------------------------------

    /** @test */
    public function testIsLoggedInReturnsTrue(): void
    {
        $this->helperData->method('isLogin')->willReturn(true);
        $this->assertTrue($this->block->isLoggedIn());
    }

    /** @test */
    public function testIsLoggedInReturnsFalse(): void
    {
        $this->helperData->method('isLogin')->willReturn(false);
        $this->assertFalse($this->block->isLoggedIn());
    }

    // -------------------------------------------------------------------------
    // getRelatedMode()
    // -------------------------------------------------------------------------

    /** @test — config value "1" returns true */
    public function testGetRelatedModeTrueWhenOne(): void
    {
        $this->helperData->method('getPostViewPageConfig')->with('related_mode')->willReturn('1');
        $this->assertTrue($this->block->getRelatedMode());
    }

    /** @test — config value "0" returns false */
    public function testGetRelatedModeFalseWhenZero(): void
    {
        $this->helperData->method('getPostViewPageConfig')->with('related_mode')->willReturn('0');
        $this->assertFalse($this->block->getRelatedMode());
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function createTagMock(string $name, string $url = ''): MockObject
    {
        $tag = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->addMethods(['getName', 'getUrl'])
            ->getMock();
        $tag->method('getName')->willReturn($name);
        $tag->method('getUrl')->willReturn($url ?: "https://example.com/tag/{$name}");
        return $tag;
    }

    private function injectLikeFactory(): void
    {
        $likeCollection = new class {
            public function addFieldToFilter($field, $value): self
            {
                return $this;
            }

            public function getSize(): int
            {
                return 0;
            }
        };

        $likeModel = new class($likeCollection) {
            private object $collection;

            public function __construct(object $collection)
            {
                $this->collection = $collection;
            }

            public function getCollection(): object
            {
                return $this->collection;
            }
        };

        $likeFactory = new class($likeModel) {
            private object $model;

            public function __construct(object $model)
            {
                $this->model = $model;
            }

            public function create(): object
            {
                return $this->model;
            }
        };

        $this->injectProperty('likeFactory', $likeFactory);
    }

    /**
     * @param array $overrides
     * @return array
     */
    private function makeCommentRow(array $overrides = []): array
    {
        return array_merge([
            'comment_id' => 1,
            'reply_id'   => 0,
            'status'     => 1,
            'is_reply'   => 0,
            'entity_id'  => 0,
            'user_name'  => 'Guest',
            'content'    => 'Nice post!',
            'created_at' => '2026-01-01 00:00:00',
            'has_reply'  => 0,
        ], $overrides);
    }
}
