<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit;

use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\WorkermanRequestHandler\OnMessage;
use Chubbyphp\WorkermanRequestHandler\PsrRequestFactoryInterface;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitterInterface;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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
    #[DoesNotPerformAssertions]
    public function testInvoke(): void
    {
        $builder = new MockObjectBuilder();

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

        /** @var WorkermanRequest $workermanRequest */
        $workermanRequest = $builder->create(WorkermanRequest::class, []);

        /** @var ServerRequestInterface $request */
        $request = $builder->create(ServerRequestInterface::class, []);

        /** @var ResponseInterface $response */
        $response = $builder->create(ResponseInterface::class, []);

        /** @var PsrRequestFactoryInterface $psrRequestFactory */
        $psrRequestFactory = $builder->create(PsrRequestFactoryInterface::class, [
            new WithReturn('create', [$workermanTcpConnection, $workermanRequest], $request),
        ]);

        /** @var WorkermanResponseEmitterInterface $workermanResponseEmitter */
        $workermanResponseEmitter = $builder->create(WorkermanResponseEmitterInterface::class, [
            new WithoutReturn('emit', [$response, $workermanTcpConnection]),
        ]);

        /** @var RequestHandlerInterface $workermanRequestHandler */
        $workermanRequestHandler = $builder->create(RequestHandlerInterface::class, [
            new WithReturn('handle', [$request], $response),
        ]);

        $onMessage = new OnMessage($psrRequestFactory, $workermanResponseEmitter, $workermanRequestHandler);
        $onMessage($workermanTcpConnection, $workermanRequest);
    }
}
