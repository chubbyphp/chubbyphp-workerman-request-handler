# chubbyphp-workerman-request-handler

[![CI](https://github.com/chubbyphp/chubbyphp-workerman-request-handler/actions/workflows/ci.yml/badge.svg)](https://github.com/chubbyphp/chubbyphp-workerman-request-handler/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-workerman-request-handler/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-workerman-request-handler?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-workerman-request-handler%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-workerman-request-handler/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-workerman-request-handler/v)](https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-workerman-request-handler/downloads)](https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-workerman-request-handler/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler)

[![bugs](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=bugs)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![code_smells](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=code_smells)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![coverage](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=coverage)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![duplicated_lines_density](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![ncloc](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=ncloc)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![sqale_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![alert_status](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=alert_status)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![reliability_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![security_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=security_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![sqale_index](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)
[![vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-workerman-request-handler&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-workerman-request-handler)

## Description

A request handler adapter for [workerman][6], using PSR-7, PSR-15 and PSR-17.

It turns a [workerman][6] HTTP `Worker` into a runtime for any [PSR-15][7] request handler: each incoming
workerman request is converted into a [PSR-7][8] server request through [PSR-17][9] factories, handed to your
application, and the returned PSR-7 response is sent back over the workerman connection.

Because workerman is a long-running process, your application is bootstrapped once per worker process and then
serves many requests. This removes the per-request bootstrap cost of a classic PHP-FPM setup.

## Requirements

 * php: ^8.3
 * [psr/http-factory][2]: ^1.1
 * [psr/http-message][3]: ^1.1|^2.0
 * [psr/http-server-handler][4]: ^1.0.2
 * [psr/log][5]: ^2.0|^3.0.2
 * [workerman/workerman][6]: ^5.2.2

## Suggest

Any [PSR-7][8] / [PSR-17][9] implementation can be used, for example:

 * [guzzlehttp/psr7][10] (with [http-interop/http-factory-guzzle][11])
 * [laminas/laminas-diactoros][12]
 * [nyholm/psr7][13]
 * [slim/psr7][14]
 * [sunrise/http-message][15]

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-workerman-request-handler][1].

```sh
composer require chubbyphp/chubbyphp-workerman-request-handler "^2.3"
```

The examples below use [slim/psr7][14] as the PSR-7 / PSR-17 implementation:

```sh
composer require slim/psr7 "^1.8"
```

## Usage

### Basic server

Create a `server.php` and start it with `php server.php start` (add `-d` to daemonize):

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\WorkermanRequestHandler\OnMessage;
use Chubbyphp\WorkermanRequestHandler\PsrRequestFactory;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitter;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Workerman\Worker;

require __DIR__.'/vendor/autoload.php';

/** @var RequestHandlerInterface $app */
$app = ...; // your PSR-15 application, bootstrapped once per worker process

$http = new Worker('http://0.0.0.0:8080');

// number of worker processes, typically the number of CPU cores
$http->count = 4;

$http->onWorkerStart = static function (): void {
    echo 'Workerman http server is started at http://0.0.0.0:8080'.PHP_EOL;
};

$http->onMessage = new OnMessage(
    new PsrRequestFactory(
        new ServerRequestFactory(),
        new StreamFactory(),
        new UploadedFileFactory()
    ),
    new WorkermanResponseEmitter(),
    $app
);

Worker::runAll();
```

### Components

The package consists of three small, replaceable parts. Each one has an interface, so you can swap in your own
implementation where the defaults don't fit.

| Class                       | Interface                            | Responsibility                                                                                                 |
|-----------------------------|--------------------------------------|----------------------------------------------------------------------------------------------------------------|
| `OnMessage`                 | `OnMessageInterface`                 | Workerman `onMessage` callback: builds the PSR-7 request, calls the PSR-15 handler and emits the response.    |
| `PsrRequestFactory`         | `PsrRequestFactoryInterface`         | Converts a workerman request into a PSR-7 server request via the given PSR-17 factories.                       |
| `WorkermanResponseEmitter`  | `WorkermanResponseEmitterInterface`  | Converts a PSR-7 response into a workerman response and sends it over the connection.                          |

`PsrRequestFactory` maps the following data:

 * method, URI and all headers
 * cookies, query params and the parsed body (form data)
 * uploaded files, including nested ones (as `UploadedFileInterface` instances)
 * the raw body, written to the request body stream
 * server params `REMOTE_ADDR` and `REMOTE_PORT` from the TCP connection

### Long-running process caveats

Workerman keeps the PHP process alive between requests, which differs from PHP-FPM:

 * Superglobals like `$_GET`, `$_POST`, `$_SERVER` or `$_COOKIE` are not populated. Read everything from the
   PSR-7 request instead.
 * Anything you keep in static properties or in long-lived services persists across requests. Avoid request
   specific state in shared objects, or reset it per request.
 * Only `REMOTE_ADDR` and `REMOTE_PORT` are available as server params. If you run behind a reverse proxy,
   use a middleware such as [chubbyphp/chubbyphp-trusted-proxy][16] to resolve the client IP from headers.

### With Blackfire

`BlackfireOnMessageAdapter` wraps an `OnMessageInterface` and profiles a request whenever the
`X-Blackfire-Query` header is present, for example when triggered by the Blackfire browser extension or the
`blackfire curl` command. Requests without that header pass through untouched. The probe is always ended,
even if the wrapped handler throws.

Requires the `blackfire` extension and the [blackfire/php-sdk][17] package.

```php
<?php

declare(strict_types=1);

namespace App;

use Blackfire\Client;
use Blackfire\Profile\Configuration;
use Chubbyphp\WorkermanRequestHandler\Adapter\BlackfireOnMessageAdapter;
use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;
use Psr\Log\LoggerInterface;

/** @var OnMessageInterface $onMessage */
$onMessage = ...;

/** @var LoggerInterface $logger */
$logger = ...;

if (extension_loaded('blackfire')) {
    $onMessage = new BlackfireOnMessageAdapter(
        $onMessage,
        new Client(),
        new Configuration(), // optional, defaults to new Configuration()
        $logger              // optional, defaults to a NullLogger; receives Blackfire client errors
    );
}

$http->onMessage = $onMessage;
```

### With New Relic

`NewRelicOnMessageAdapter` wraps an `OnMessageInterface` and starts a New Relic transaction for every request.
The transaction is always ended, even if the wrapped handler throws, so each request is reported separately
instead of as one endless transaction per worker process.

Requires the `newrelic` extension.

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\WorkermanRequestHandler\Adapter\NewRelicOnMessageAdapter;
use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;

/** @var OnMessageInterface $onMessage */
$onMessage = ...;

if (extension_loaded('newrelic') && false !== $appname = ini_get('newrelic.appname')) {
    $onMessage = new NewRelicOnMessageAdapter($onMessage, $appname);
}

$http->onMessage = $onMessage;
```

Both adapters implement `OnMessageInterface`, so they can be combined by nesting them.

## Copyright

2026 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler
[2]: https://packagist.org/packages/psr/http-factory
[3]: https://packagist.org/packages/psr/http-message
[4]: https://packagist.org/packages/psr/http-server-handler
[5]: https://packagist.org/packages/psr/log
[6]: https://packagist.org/packages/workerman/workerman
[7]: https://www.php-fig.org/psr/psr-15
[8]: https://www.php-fig.org/psr/psr-7
[9]: https://www.php-fig.org/psr/psr-17
[10]: https://packagist.org/packages/guzzlehttp/psr7
[11]: https://packagist.org/packages/http-interop/http-factory-guzzle
[12]: https://packagist.org/packages/laminas/laminas-diactoros
[13]: https://packagist.org/packages/nyholm/psr7
[14]: https://packagist.org/packages/slim/psr7
[15]: https://packagist.org/packages/sunrise/http-message
[16]: https://packagist.org/packages/chubbyphp/chubbyphp-trusted-proxy
[17]: https://packagist.org/packages/blackfire/php-sdk
