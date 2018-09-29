<?php

declare(strict_types=1);

namespace ManoManoTech\CorrelationIdHTTPlug\Tests;

use GuzzleHttp\Psr7\Request;
use Http\Client\Common\PluginClient;
use Http\Mock\Client;
use ManoManoTech\CorrelationId\CorrelationEntryNameInterface;
use ManoManoTech\CorrelationId\CorrelationIdContainerInterface;
use ManoManoTech\CorrelationIdHTTPlug\CorrelationIdHTTPlug;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @covers \ManoManoTech\CorrelationIdHTTPlug\CorrelationIdHTTPlug
 */
final class CorrelationIdHTTPlugTest extends TestCase
{
    /** @dataProvider provider */
    public function testAddRequestIdentifier(
        string $currentRequestId,
        ?string $parentRequestId,
        ?string $rootRequestId,
        ?string $expectedParentHeaderValue,
        ?string $expectedRootHeaderValue
    ): void {
        $rootHeaderName = 'root';
        $parentHeaderName = 'parent';
        $requestIdentifierMock = $this->createMock(CorrelationIdContainerInterface::class);
        $requestIdentifierMock->expects(self::any())
                              ->method('current')
                              ->willReturn($currentRequestId);
        $requestIdentifierMock->expects(self::any())
                              ->method('parent')
                              ->willReturn($parentRequestId);
        $requestIdentifierMock->expects(self::any())
                              ->method('root')
                              ->willReturn($rootRequestId);

        $correlationEntryName = $this->createMock(CorrelationEntryNameInterface::class);
        $correlationEntryName->expects(self::once())
                             ->method('parent')
                             ->willReturn($parentHeaderName);
        $correlationEntryName->expects(self::once())
                             ->method('root')
                             ->willReturn($rootHeaderName);

        $request = new Request('GET', 'http://example.com/foo');

        $client = new Client();
        $pluginClient = new PluginClient(
            $client,
            [new CorrelationIdHTTPlug($requestIdentifierMock, $correlationEntryName)]
        );

        // run
        $pluginClient->sendRequest($request);

        $lastRequest = $client->getLastRequest();
        // test

        static::assertNotFalse($lastRequest);
        static::assertInstanceOf(RequestInterface::class, $lastRequest);
        static::assertArrayHasKey($parentHeaderName, $lastRequest->getHeaders());
        static::assertEquals([$expectedParentHeaderValue], $lastRequest->getHeader($parentHeaderName));
        static::assertArrayHasKey($rootHeaderName, $lastRequest->getHeaders());
        static::assertEquals([$expectedRootHeaderValue], $lastRequest->getHeader($rootHeaderName));
    }

    public function provider(): array
    {
        return [
            'When the current request has no parent nor root correlation id, it should send the current correlation id as root and parent' => [
                'current_request_id',
                null,
                null,
                'current_request_id',
                'current_request_id',
            ],
            'When the current request has a parent but no root correlation id, it should send the parent correlation id as root and the current request id as parent' => [
                'current_request_id',
                'parent_request_id',
                null,
                'current_request_id',
                'parent_request_id',
            ],
            'When the current request has a root but no parent correlation id, it should send the root correlation id as root and the current request id as parent' => [
                'current_request_id',
                null,
                'root_request_id',
                'current_request_id',
                'root_request_id',
            ],
            'When the current request has both a root and a parent correlation id, it should send the root correlation id as root and the current request id as parent' => [
                'current_request_id',
                'parent_request_id',
                'root_request_id',
                'current_request_id',
                'root_request_id',
            ],
        ];
    }
}
