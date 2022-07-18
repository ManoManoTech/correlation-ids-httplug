# Inactive

**📢 Note:** This repository is not maintained any more.

HTTPlug Request Correlation
===========================

Injects correlation headers in requests made with [httplug]

[httplug]: http://docs.php-http.org/en/latest/index.html

Installation
------------

```bash
composer require manomano-tech/correlation-ids-httplug
```

Usage
-----

First, generate a CorrelationIdContainer:

```php
use ManoManoTech\CorrelationId\Factory\CorrelationIdContainerFactory;
use ManoManoTech\CorrelationId\Generator\RamseyUuidGenerator;

// We specify which generator will be responsible for generating the
// identification of the current process
$generator = new RamseyUuidGenerator();

$factory = new CorrelationIdContainerFactory($generator);
$correlationIdContainer = $factory->create(
    // can be any unique string
    '3fc044d9-90fa-4b50-b6d9-3423f567155f',
    // can be any unique string
    '3b5263fa-1644-4750-8f11-aaf61e58cd9e'
);
```

Then, you have two options:

### Add Plugin to your HTTPlug client

```php
use Http\Client\Common\PluginClient;
use ManoManoTech\CorrelationIdHTTPlug\CorrelationIdHTTPlug;

// create the middleware
$requestIdPlugin = new CorrelationIdHTTPlug($correlationIdContainer);

// $client = your client
$pluginClient = new PluginClient($client, [$requestIdPlugin]);
```

### Use the factory to create an HTTPlug client

```php
use ManoManoTech\CorrelationIdHTTPlug\HttpClientFactory;

// create the middleware
$factory = new HttpClientFactory($correlationIdContainer);

// add some more plugins
// $factory->addPlugin(...);

// return an instance of Http\Client\Common\PluginClien
$client = $factory->create();
```

Customizing header names
------------------------

By default, request headers will look something like this:

```http
GET / HTTP/1.1
Host: example.com
parent-request-id: 3fc044d9-90fa-4b50-b6d9-3423f567155f
root-request-id: 3b5263fa-1644-4750-8f11-aaf61e58cd9e
```

You can change this by providing a second argument to the constructor:

```php
use Http\Client\Common\PluginClient;
use ManoManoTech\CorrelationId\CorrelationEntryName;
use ManoManoTech\CorrelationIdHTTPlug\CorrelationIdHTTPlug;

// first argument is not used in this context
$correlationEntryName = new CorrelationEntryName('current-id', 'parent-id', 'root-id');
$requestIdPlugin = new CorrelationIdHTTPlug($correlationIdContainer, $correlationEntryName);

// $client = your client
$pluginClient = new PluginClient($client, [$requestIdPlugin]);
```

or, if you use the factory:

```php
use ManoManoTech\CorrelationId\CorrelationEntryName;
use ManoManoTech\CorrelationIdHTTPlug\HttpClientFactory;

// first argument is not used in this context
$correlationEntryName = new CorrelationEntryName(
    'current-id', // not used in this context
    'parent-id',
    'root-id'
);
$factory = new HttpClientFactory($correlationIdContainer, $correlationEntryName);

// add some more plugins
// $factory->addPlugin(...);

// return an instance of Http\Client\Common\PluginClien
$client = $factory->create();
```

will both produce:

```http
GET / HTTP/1.1
Host: example.com
parent-id: 3fc044d9-90fa-4b50-b6d9-3423f567155f
root-id: 3b5263fa-1644-4750-8f11-aaf61e58cd9e
```
