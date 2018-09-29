<?php

declare(strict_types=1);

namespace ManoManoTech\CorrelationIdHTTPlug\Tests;

use GuzzleHttp\Psr7\Request;
use Http\Mock\Client;
use ManoManoTech\CorrelationId\CorrelationEntryNameInterface;
use ManoManoTech\CorrelationId\CorrelationIdContainerInterface;
use ManoManoTech\CorrelationIdHTTPlug\HttpClientFactory;
use PHPUnit\Framework\TestCase;

/** @covers \ManoManoTech\CorrelationIdHTTPlug\HttpClientFactory */
final class HttpClientFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        // init
        $correlationIdContainer = $this->createMock(CorrelationIdContainerInterface::class);
        $correlationIdContainer->expects(self::any())
                               ->method('current')
                               ->willReturn('foo');
        $correlationIdContainer->expects(self::any())
                               ->method('parent')
                               ->willReturn('bar');
        $correlationIdContainer->expects(self::any())
                               ->method('root')
                               ->willReturn('baz');

        $request = new Request('GET', 'http://example.com/foo');

        $object = new HttpClientFactory($correlationIdContainer);
        $client = new Client();

        // run
        $result = $object->create($client);
        $result->sendRequest($request);

        // test
        $lastRequest = $client->getLastRequest();
        static::assertNotFalse($lastRequest);
        static::assertArrayHasKey('parent-correlation-id', $lastRequest->getHeaders());
        static::assertEquals(['foo'], $lastRequest->getHeader('parent-correlation-id'));
        static::assertArrayHasKey('root-correlation-id', $lastRequest->getHeaders());
        static::assertEquals(['baz'], $lastRequest->getHeader('root-correlation-id'));
    }

    public function testCreateWithCustomHeaders(): void
    {
        // init
        $correlationIdContainer = $this->createMock(CorrelationIdContainerInterface::class);
        $correlationIdContainer->expects(self::any())
                               ->method('current')
                               ->willReturn('foo');
        $correlationIdContainer->expects(self::any())
                               ->method('parent')
                               ->willReturn('bar');
        $correlationIdContainer->expects(self::any())
                               ->method('root')
                               ->willReturn('baz');

        $correlationEntryName = $this->createMock(CorrelationEntryNameInterface::class);
        $correlationEntryName->expects(self::any())
                             ->method('current')
                             ->willReturn('current-id');
        $correlationEntryName->expects(self::any())
                             ->method('parent')
                             ->willReturn('parent-id');
        $correlationEntryName->expects(self::any())
                             ->method('root')
                             ->willReturn('root-id');

        $request = new Request('GET', 'http://example.com/foo');

        $object = new HttpClientFactory($correlationIdContainer, $correlationEntryName);
        $client = new Client();

        // run
        $result = $object->create($client);
        $result->sendRequest($request);

        // test
        $lastRequest = $client->getLastRequest();
        static::assertNotFalse($lastRequest);
        static::assertArrayHasKey('parent-id', $lastRequest->getHeaders());
        static::assertEquals(['foo'], $lastRequest->getHeader('parent-id'));
        static::assertArrayHasKey('root-id', $lastRequest->getHeaders());
        static::assertEquals(['baz'], $lastRequest->getHeader('root-id'));
    }
}
