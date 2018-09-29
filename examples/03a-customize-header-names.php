<?php

declare(strict_types=1);
require '01-generate-correlation-id-container.php';

use Http\Client\Common\PluginClient;
use ManoManoTech\CorrelationId\CorrelationEntryName;
use ManoManoTech\CorrelationIdHTTPlug\CorrelationIdHTTPlug;

// first argument is not used in this context
$correlationEntryName = new CorrelationEntryName('current-id', 'parent-id', 'root-id');
$requestIdPlugin = new CorrelationIdHTTPlug($correlationIdContainer, $correlationEntryName);

// $client = your client
$pluginClient = new PluginClient($client, [$requestIdPlugin]);
