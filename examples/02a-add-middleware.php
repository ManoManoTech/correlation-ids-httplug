<?php

declare(strict_types=1);
require '01-generate-correlation-id-container.php';

use Http\Client\Common\PluginClient;
use ManoManoTech\CorrelationIdHTTPlug\CorrelationIdHTTPlug;

// create the middleware
$requestIdPlugin = new CorrelationIdHTTPlug($correlationIdContainer);

// $client = your client
$pluginClient = new PluginClient($client, [$requestIdPlugin]);
