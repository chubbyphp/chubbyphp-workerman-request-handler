# chubbyphp-workerman-request-handler

[![CI](https://github.com/chubbyphp/chubbyphp-workerman-request-handler/workflows/CI/badge.svg?branch=master)](https://github.com/chubbyphp/chubbyphp-workerman-request-handler/actions?query=workflow%3ACI)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-workerman-request-handler/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-workerman-request-handler?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-workerman-request-handler%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-workerman-request-handler/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-workerman-request-handler/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-workerman-request-handler/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler)
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

A request handler adapter for workerman, using PSR-7, PSR-15 and PSR-17.

## Requirements

 * php: ^8.1
 * [psr/http-factory][2]: ^1.0.2
 * [psr/http-message][3]: ^1.1|^2.0
 * [psr/http-server-handler][4]: ^1.0.2
 * [psr/log][5]: ^2.0|^3.0
 * [workerman/workerman][6]: ^4.1.13

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-workerman-request-handler][1].

```sh
composer require chubbyphp/chubbyphp-workerman-request-handler "^2.1"
```

## Usage

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\WorkermanRequestHandler\OnMessage;
use Chubbyphp\WorkermanRequestHandler\PsrRequestFactory;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitter;
use Psr\Http\Server\RequestHandlerInterface;
use Some\Psr17\Factory\ServerRequestFactory;
use Some\Psr17\Factory\StreamFactory;
use Some\Psr17\Factory\UploadedFileFactory;
use Workerman\Worker;

$loader = require __DIR__.'/vendor/autoload.php';

/** @var RequestHandlerInterface $app*/
$app = ...;

$http = new Worker('http://0.0.0.0:8080');

$http->count = 4;

$http->onWorkerStart = function () {
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

### with blackfire

```php
<?php

declare(strict_types=1);

namespace App;

use Blackfire\Client;
use Chubbyphp\WorkermanRequestHandler\Adapter\BlackfireOnMessageAdapter;
use Chubbyphp\WorkermanRequestHandler\OnMessage;

/** @var OnMessage $onMessage */
$onMessage = ...;

if (extension_loaded('blackfire') {
    $onMessage = new BlackfireOnMessageAdapter($onMessage, new Client());
}

$http->onMessage = $onMessage;
```

### with newrelic

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\WorkermanRequestHandler\Adapter\NewRelicOnMessageAdapter;
use Chubbyphp\WorkermanRequestHandler\OnMessage;

/** @var OnMessage $onMessage */
$onMessage = ...;

if (extension_loaded('newrelic') && false !== $name = ini_get('newrelic.appname')) {
    $onMessage = new NewRelicOnMessageAdapter($onMessage, $name);
}

$http->onMessage = $onMessage;
```

## Copyright

2024 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-workerman-request-handler
[2]: https://packagist.org/packages/psr/http-factory
[3]: https://packagist.org/packages/psr/http-message
[4]: https://packagist.org/packages/psr/http-server-handler
[5]: https://packagist.org/packages/psr/log
[6]: https://packagist.org/packages/workerman/workerman
