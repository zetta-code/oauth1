<?php

namespace Risan\OAuth1\Test\Unit\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Risan\OAuth1\Config\UriConfig;
use Risan\OAuth1\Request\UriParser;
use Risan\OAuth1\Request\UriParserInterface;

class UriConfigTest extends TestCase
{
    private $uris;
    private $uriParser;
    private $uriConfig;

    function setUp(): void
    {
        $this->uris = [
            'base_uri' => 'http://example.com',
            'temporary_credentials_uri' => '/request_token',
            'authorization_uri' => '/authorize',
            'token_credentials_uri' => '/access_token',
            'callback_uri' => 'http://johndoe.com',
        ];

        $this->uriParser = new UriParser;
        $this->uriConfig = new UriConfig($this->uris, $this->uriParser);
    }

    /** @test */
    function it_can_get_parser()
    {
        $this->assertInstanceOf(UriParserInterface::class, $this->uriConfig->getParser());
    }

    /** @test */
    function it_can_be_set_from_array()
    {
        $this->assertSame($this->uriConfig, $this->uriConfig->setFromArray([
            'base_uri' => 'http://example.net',
            'temporary_credentials_uri' => '/request_token',
            'authorization_uri' => '/authorize',
            'token_credentials_uri' => '/access_token',
            'callback_uri' => 'http://johndoe.net',
        ]));

        $this->assertEquals('http://example.net', (string) $this->uriConfig->base());
        $this->assertEquals('http://example.net/request_token', (string) $this->uriConfig->forTemporaryCredentials());
        $this->assertEquals('http://example.net/authorize', (string) $this->uriConfig->forAuthorization());
        $this->assertEquals('http://example.net/access_token', (string) $this->uriConfig->forTokenCredentials());
        $this->assertEquals('http://johndoe.net', (string) $this->uriConfig->callback());
    }

    /** @test */
    function it_can_validate_uris()
    {
        // Valid uris.
        $this->assertTrue($this->uriConfig->validateUris($this->uris));

        // Invalid uris.
        $this->expectException(InvalidArgumentException::class);
        $this->uriConfig->validateUris(['foo' => 'bar']);
    }

    /** @test */
    function it_can_set_base()
    {
        $baseUri = $this->uriParser->toPsrUri('http://example.net');
        $this->assertSame($this->uriConfig, $this->uriConfig->setBase($baseUri));
        $this->assertEquals('http://example.net', (string) $this->uriConfig->base());
    }

    /** @test */
    function it_throws_exception_if_base_uri_is_relative()
    {
        $relativeUri = $this->uriParser->toPsrUri('/foo');
        $this->expectException(InvalidArgumentException::class);
        $this->uriConfig->setBase($relativeUri);
    }

    /** @test */
    function it_can_get_base()
    {
        $this->assertInstanceOf(UriInterface::class, $this->uriConfig->base());
        $this->assertEquals('http://example.com', (string) $this->uriConfig->base());
    }

    /** @test */
    function it_can_check_if_base_uri_is_set()
    {
        // Has base URI.
        $this->assertTrue($this->uriConfig->hasBase());

        // Without base URI.
        $uriConfig = new UriConfig([
            'temporary_credentials_uri' => '/request_token',
            'authorization_uri' => '/authorize',
            'token_credentials_uri' => '/access_token',
        ], $this->uriParser);

        $this->assertFalse($uriConfig->hasBase());
    }

    /** @test */
    function it_can_get_temporary_credentials()
    {
        $this->assertInstanceOf(UriInterface::class, $this->uriConfig->forTemporaryCredentials());
        $this->assertEquals('http://example.com/request_token', (string) $this->uriConfig->forTemporaryCredentials());
    }

    /** @test */
    function it_can_get_authorization()
    {
        $this->assertInstanceOf(UriInterface::class, $this->uriConfig->forAuthorization());
        $this->assertEquals('http://example.com/authorize', (string) $this->uriConfig->forAuthorization());
    }

    /** @test */
    function it_can_get_token_credentials()
    {
        $this->assertInstanceOf(UriInterface::class, $this->uriConfig->forTokenCredentials());
        $this->assertEquals('http://example.com/access_token', (string) $this->uriConfig->forTokenCredentials());
    }

    /** @test */
    function it_can_get_callback()
    {
        $this->assertInstanceOf(UriInterface::class, $this->uriConfig->callback());
        $this->assertEquals('http://johndoe.com', (string) $this->uriConfig->callback());
    }

    /** @test */
    function it_can_check_if_callback_uri_is_set()
    {
        // Has base URI.
        $this->assertTrue($this->uriConfig->hasCallback());

        // Without base URI.
        $uriConfig = new UriConfig([
            'temporary_credentials_uri' => '/request_token',
            'authorization_uri' => '/authorize',
            'token_credentials_uri' => '/access_token',
        ], $this->uriParser);

        $this->assertFalse($uriConfig->hasCallback());
    }

    /** @test */
    function it_can_build_uri()
    {
        // Resolve relative URI.
        $uri = $this->uriConfig->build('/foo');
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('http://example.com/foo', (string) $uri);

        // Resolve absolute URI.
        $uri = $this->uriConfig->build('http://example.net/foo');
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('http://example.net/foo', (string) $uri);

        // Missing scheme.
        $missingScheme = $this->uriParser->toPsrUri('http://example.net')->withScheme('');
        $uri = $this->uriConfig->build($missingScheme);
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('http://example.net', (string) $uri);
    }

    /** @test */
    function it_can_check_if_uri_should_be_resolved_to_absolute_uri()
    {
        // Resolve relative URI.
        $uri = $this->uriParser->toPsrUri('/foo');
        $this->assertTrue($this->uriConfig->shouldBeResolvedToAbsoluteUri($uri));

        // Resolve absolute URI.
        $uri = $this->uriParser->toPsrUri('http://example.net');
        $this->assertFalse($this->uriConfig->shouldBeResolvedToAbsoluteUri($uri));
    }
}
