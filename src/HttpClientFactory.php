<?php

declare(strict_types=1);

namespace ManoManoTech\CorrelationIdHTTPlug;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use ManoManoTech\CorrelationId\CorrelationEntryNameInterface;
use ManoManoTech\CorrelationId\CorrelationIdContainerInterface;

final class HttpClientFactory
{
    /** @var Plugin[] */
    private $plugins = [];

    public function __construct(
        CorrelationIdContainerInterface $requestIdentifier,
        CorrelationEntryNameInterface $correlationEntryName = null
    ) {
        $this->addPlugin(new CorrelationIdHTTPlug($requestIdentifier, $correlationEntryName));
    }

    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    /** Build the HTTP client to talk to the API. */
    public function create(HttpClient $client = null): PluginClient
    {
        return new PluginClient($client ?? HttpClientDiscovery::find(), $this->plugins);
    }
}
