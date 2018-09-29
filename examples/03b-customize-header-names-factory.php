<?php

declare(strict_types=1);
require '01-generate-correlation-id-container.php';

use ManoManoTech\CorrelationId\CorrelationEntryName;
use ManoManoTech\CorrelationIdHTTPlug\HttpClientFactory;

// first argument is not used in this context
$correlationEntryName = new CorrelationEntryName(
    'current-id',
    'parent-id',
    'root-id'
);
$factory = new HttpClientFactory($correlationIdContainer, $correlationEntryName);

// add some more plugins
// $factory->addPlugin(...);

// return an instance of Http\Client\Common\PluginClien
$client = $factory->create();
