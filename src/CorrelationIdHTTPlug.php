<?php

declare(strict_types=1);

namespace ManoManoTech\CorrelationIdHTTPlug;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use ManoManoTech\CorrelationId\CorrelationEntryName;
use ManoManoTech\CorrelationId\CorrelationEntryNameInterface;
use ManoManoTech\CorrelationId\CorrelationIdContainerInterface;
use Psr\Http\Message\RequestInterface;

final class CorrelationIdHTTPlug implements Plugin
{
    /** @var CorrelationEntryNameInterface */
    private $correlationEntryName;
    /** @var CorrelationIdContainerInterface */
    private $requestIdentifier;

    public function __construct(
        CorrelationIdContainerInterface $requestIdentifier,
        CorrelationEntryNameInterface $correlationEntryName = null
    ) {
        $this->requestIdentifier = $requestIdentifier;
        $this->correlationEntryName = $correlationEntryName ?? CorrelationEntryName::suffixed();
    }

    /**
     * Handle the request and return the response coming from the next callable.
     *
     * @see http://docs.php-http.org/en/latest/plugins/build-your-own.html
     *
     * @param callable $next  Next middleware in the chain, the request is passed as the first argument
     * @param callable $first First middleware in the chain, used to to restart a request
     *
     * @return Promise resolves a PSR-7 Response or fails with an Http\Client\Exception (The same as HttpAsyncClient)
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $headers = [
            $this->correlationEntryName->parent() => $this->requestIdentifier->current(),
            $this->correlationEntryName->root() => $this->selectBestRootHeaderValue(),
        ];

        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $next($request);
    }

    private function selectBestRootHeaderValue(): string
    {
        // Best value is, in the following order:
        // - root value if set or else
        // - parent value if set or else
        // - current value
        $rootPossibleValues = array_filter(
            [
                $this->requestIdentifier->root(),
                $this->requestIdentifier->parent(),
                $this->requestIdentifier->current(),
            ]
        );

        return array_shift($rootPossibleValues) ?? '';
    }
}
