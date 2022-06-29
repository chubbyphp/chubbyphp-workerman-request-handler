<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit;

use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\WorkermanRequestHandler\OnMessage;
use Chubbyphp\WorkermanRequestHandler\PsrRequestFactoryInterface;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

/**
 * @covers \Chubbyphp\WorkermanRequestHandler\OnMessage
 *
 * @internal
 */
final class OnMessageTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvoke(): void
    {
        /** @var MockObject|WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class);

        /** @var MockObject|WorkermanRequest $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class);

        /** @var MockObject|ResponseInterface $response */
        $response = $this->getMockByCalls(ResponseInterface::class);

        /** @var MockObject|PsrRequestFactoryInterface $psrRequestFactory */
        $psrRequestFactory = $this->getMockByCalls(PsrRequestFactoryInterface::class, [
            Call::create('create')->with($workermanTcpConnection, $workermanRequest)->willReturn($request),
        ]);

        /** @var MockObject|WorkermanResponseEmitterInterface $workermanResponseEmitter */
        $workermanResponseEmitter = $this->getMockByCalls(WorkermanResponseEmitterInterface::class, [
            Call::create('emit')->with($response, $workermanTcpConnection),
        ]);

        /** @var MockObject|RequestHandlerInterface $workermanRequestHandler */
        $workermanRequestHandler = $this->getMockByCalls(RequestHandlerInterface::class, [
            Call::create('handle')->with($request)->willReturn($response),
        ]);

        $onMessage = new OnMessage($psrRequestFactory, $workermanResponseEmitter, $workermanRequestHandler);
        $onMessage($workermanTcpConnection, $workermanRequest);
    }
}
