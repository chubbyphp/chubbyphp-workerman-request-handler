<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

interface OnMessageInterface
{
    public function __invoke(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): void;
}
