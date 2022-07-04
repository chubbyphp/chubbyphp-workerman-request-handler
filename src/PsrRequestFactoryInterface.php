<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Psr\Http\Message\ServerRequestInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

interface PsrRequestFactoryInterface
{
    public function create(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): ServerRequestInterface;
}
