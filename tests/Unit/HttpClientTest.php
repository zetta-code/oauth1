<?php

namespace Risan\OAuth1\Test\Unit;

use Risan\OAuth1\HttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as Guzzle;
use Risan\OAuth1\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Risan\OAuth1\Request\RequestInterface;

class HttpClientTest extends TestCase
{
    private $guzzleStub;
    private $requestStub;
    private $responseStub;
    private $httpClient;
    private $httpClientStub;

    function setUp(): void
    {
        $this->guzzleStub = $this->createMock(Guzzle::class);
        $this->responseStub = $this->createMock(Response::class);
        $this->requestStub = $this->createMock(RequestInterface::class);
        $this->httpClient = new HttpClient($this->guzzleStub);

        $this->httpClientStub = $this->getMockBuilder(HttpClient::class)
            ->setConstructorArgs([$this->guzzleStub])
            ->setMethods(['request'])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }

    /** @test */
    function it_implements_http_client_interface()
    {
        $this->assertInstanceOf(HttpClientInterface::class, $this->httpClient);
    }

    /** @test */
    function it_can_get_guzzle_instance()
    {
        $this->assertInstanceOf(Guzzle::class, $this->httpClient->getGuzzle());
    }

    /** @test */
    function it_can_send_request()
    {
        $this->guzzleStub
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://example.com', ['foo' => 'bar'])
            ->willReturn($this->responseStub);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->httpClient->request('POST', 'http://example.com', ['foo' => 'bar'])
        );
    }

    /** @test */
    function it_can_send_request_with_request_instance()
    {
        $this->requestStub
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestStub
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('http://example.com');

        $this->requestStub
            ->expects($this->once())
            ->method('getOptions')
            ->willReturn(['foo' => 'bar']);

        $this->httpClientStub
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://example.com', ['foo' => 'bar'])
            ->willReturn($this->responseStub);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->httpClientStub->send($this->requestStub)
        );
    }

    /** @test */
    function it_can_send_post_request()
    {
        $this->httpClientStub
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://example.com', ['foo' => 'bar'])
            ->willReturn($this->responseStub);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->httpClientStub->post('http://example.com', ['foo' => 'bar'])
        );
    }
}
