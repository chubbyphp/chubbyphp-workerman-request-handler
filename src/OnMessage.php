<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Psr\Http\Server\RequestHandlerInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

final class OnMessage implements OnMessageInterface
{
    public function __construct(
        private readonly PsrRequestFactoryInterface $psrRequestFactory,
        private readonly WorkermanResponseEmitterInterface $workermanResponseEmitter,
        private readonly RequestHandlerInterface $requestHander
    ) {}

    public function __invoke(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): void
    {
        $this->workermanResponseEmitter->emit(
            $this->requestHander->handle($this->psrRequestFactory->create($workermanTcpConnection, $workermanRequest)),
            $workermanTcpConnection
        );
    }
}
