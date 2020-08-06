<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Response as WorkermanResponse;

final class WorkermanResponseEmitter implements WorkermanResponseEmitterInterface
{
    public function emit(ResponseInterface $response, WorkermanTcpConnection $workermanTcpConnection): void
    {
        $workermanTcpConnection->send(
            (new WorkermanResponse())
                ->withStatus($response->getStatusCode(), $response->getReasonPhrase())
                ->withHeaders($response->getHeaders())
                ->withBody((string) $response->getBody())
        );
    }
}
