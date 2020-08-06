<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;

interface WorkermanResponseEmitterInterface
{
    public function emit(ResponseInterface $response, WorkermanTcpConnection $workermanTcpConnection): void;
}
