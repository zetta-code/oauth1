<?php

namespace Risan\OAuth1\Test\Unit\Request;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Risan\OAuth1\Request\UriParser;
use Risan\OAuth1\Request\UriParserInterface;

class UriParserTest extends TestCase
{
    private $uriParser;
    private $psrUriStub;

    function setUp(): void
    {
        $this->uriParser = new UriParser;
        $this->psrUriStub = $this->createMock(UriInterface::class);
    }

    /** @test */
    function it_implements_uri_parser_interface()
    {
        $this->assertInstanceOf(UriParserInterface::class, $this->uriParser);
    }

     /** @test */
    function it_can_parse_to_psr_uri()
    {
        $this->assertSame($this->psrUriStub, $this->uriParser->toPsrUri($this->psrUriStub));

        $this->assertInstanceOf(UriInterface::class, $this->uriParser->toPsrUri('http://example.com'));
    }

     /** @test */
    function it_throws_invalid_argument_exception_when_converting_invalid_type()
    {
        // Boolean.
        $this->expectException(InvalidArgumentException::class);
        $this->uriParser->toPsrUri(true);

        // Integer.
        $this->expectException(InvalidArgumentException::class);
        $this->uriParser->toPsrUri(10);

        // Array.
        $this->expectException(InvalidArgumentException::class);
        $this->uriParser->toPsrUri(['foo' => 'bar']);
    }

    /** @test */
    function it_can_check_if_uri_is_absolute()
    {
        $absoluteUri = $this->uriParser->toPsrUri('http://example.com');
        $this->assertTrue($this->uriParser->isAbsolute($absoluteUri));
    }

    /** @test */
    function it_can_check_if_uri_is_not_absolute()
    {
        $relativeUri = $this->uriParser->toPsrUri('/foo');
        $this->assertFalse($this->uriParser->isAbsolute($relativeUri));
    }

    /** @test */
    function it_can_check_if_scheme_is_missing()
    {
        $missingScheme = $this->uriParser->toPsrUri('http://example.com')->withScheme('');
        $this->assertTrue($this->uriParser->isMissingScheme($missingScheme));
    }

    /** @test */
    function it_can_check_if_scheme_is_not_missing()
    {
        $withScheme = $this->uriParser->toPsrUri('http://example.com');
        $this->assertFalse($this->uriParser->isMissingScheme($withScheme));
    }

    /** @test */
    function it_can_be_built_from_parts()
    {
        $uri = $this->uriParser->buildFromParts([
            'scheme' => 'http',
            'host' => 'example.com',
            'port' => '8080',
            'path' => 'foo',
            'query' => 'bar=baz',
        ]);

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('http://example.com:8080/foo?bar=baz', (string) $uri);
    }

    /** @test */
    function it_can_resolve_relative_uri()
    {
        $baseUri = $this->uriParser->toPsrUri('http://example.com');
        $relativeUri = $this->uriParser->toPsrUri('/foo');

        $this->assertEquals(
            'http://example.com/foo',
            (string) $this->uriParser->resolve($baseUri, $relativeUri)
        );
    }

    /** @test */
    function it_can_resolve_absolute_uri()
    {
        $baseUri = $this->uriParser->toPsrUri('http://example.com');
        $absoluteUri = $this->uriParser->toPsrUri('http://foo.bar/baz');

        $this->assertEquals(
            'http://foo.bar/baz',
            (string) $this->uriParser->resolve($baseUri, $absoluteUri)
        );
    }

    /** @test */
    function it_can_append_query_parameters()
    {
        $uri = $this->uriParser->toPsrUri('http://example.com?foo=bar');

        $this->assertEquals(
            'http://example.com?foo=bar&baz=qux',
            (string) $this->uriParser->appendQueryParameters($uri, ['baz' => 'qux'])
        );
    }
}
