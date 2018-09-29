<?php

declare(strict_types=1);
require '01-generate-correlation-id-container.php';

use ManoManoTech\CorrelationIdHTTPlug\HttpClientFactory;

// create the middleware
$factory = new HttpClientFactory($correlationIdContainer);

// add some more plugins
// $factory->addPlugin(...);

// return an instance of Http\Client\Common\PluginClien
$client = $factory->create();
