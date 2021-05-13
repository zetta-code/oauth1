<?php

namespace Risan\OAuth1\Test\Unit\Request;

use PHPUnit\Framework\TestCase;
use Risan\OAuth1\Request\AuthorizationHeader;
use Risan\OAuth1\Credentials\TokenCredentials;
use Risan\OAuth1\Credentials\TemporaryCredentials;
use Risan\OAuth1\Request\ProtocolParameterInterface;

class AuthorizationHeaderTest extends TestCase
{
    private $protocolParameterStub;
    private $authorizationHeader;
    private $temporaryCredentialsStub;
    private $tokenCredentialsStub;

    function setUp(): void
    {
        $this->protocolParameterStub = $this->createMock(ProtocolParameterInterface::class);
        $this->authorizationHeader = new AuthorizationHeader($this->protocolParameterStub);
        $this->temporaryCredentialsStub = $this->createMock(TemporaryCredentials::class);
        $this->tokenCredentialsStub = $this->createMock(TokenCredentials::class);
    }

    /** @test */
    function it_can_get_protocol_parameter()
    {
        $this->assertSame($this->protocolParameterStub, $this->authorizationHeader->getProtocolParameter());
    }

    /** @test */
    function it_can_get_config()
    {
        $this->protocolParameterStub
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $this->authorizationHeader->getConfig());
    }

    /** @test */
    function it_can_normalize_protocol_parameters()
    {
        $parameters = [
            'foo' => 'bar',
            'full name' => 'john doe',
        ];

        $this->assertEquals(
            'OAuth foo="bar", full%20name="john%20doe"',
            $this->authorizationHeader->normalizeProtocolParameters($parameters)
        );
    }

    /** @test */
    function it_can_build_for_temporary_credentials()
    {
        $this->protocolParameterStub
            ->expects($this->once())
            ->method('forTemporaryCredentials')
            ->willReturn(['foo' => 'bar']);

        $this->assertEquals(
            'OAuth foo="bar"',
            $this->authorizationHeader->forTemporaryCredentials()
        );
    }

    /** @test */
    function it_can_build_for_token_credentials()
    {
        $this->protocolParameterStub
            ->expects($this->once())
            ->method('forTokenCredentials')
            ->with($this->temporaryCredentialsStub, 'verification_code')
            ->willReturn(['foo' => 'bar']);

        $this->assertEquals(
            'OAuth foo="bar"',
            $this->authorizationHeader->forTokenCredentials($this->temporaryCredentialsStub, 'verification_code')
        );
    }

    /** @test */
    function it_can_build_for_protected_resource()
    {
        $this->protocolParameterStub
            ->expects($this->once())
            ->method('forProtectedResource')
            ->with($this->tokenCredentialsStub, 'GET', 'http://example.com', ['foo' => 'bar'])
            ->willReturn(['foo' => 'bar']);

        $this->assertEquals(
            'OAuth foo="bar"',
            $this->authorizationHeader->forProtectedResource($this->tokenCredentialsStub, 'GET', 'http://example.com', ['foo' => 'bar'])
        );
    }
}
