<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Psr\Http\Server\RequestHandlerInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

final class OnMessage implements OnMessageInterface
{
    /**
     * @var PsrRequestFactoryInterface
     */
    private $psrRequestFactory;

    /**
     * @var WorkermanResponseEmitterInterface
     */
    private $workermanResponseEmitter;

    /**
     * @var RequestHandlerInterface
     */
    private $requestHander;

    public function __construct(
        PsrRequestFactoryInterface $psrRequestFactory,
        WorkermanResponseEmitterInterface $workermanResponseEmitter,
        RequestHandlerInterface $requestHander
    ) {
        $this->psrRequestFactory = $psrRequestFactory;
        $this->workermanResponseEmitter = $workermanResponseEmitter;
        $this->requestHander = $requestHander;
    }

    public function __invoke(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): void
    {
        $this->workermanResponseEmitter->emit(
            $this->requestHander->handle($this->psrRequestFactory->create($workermanRequest)),
            $workermanTcpConnection
        );
    }
}
