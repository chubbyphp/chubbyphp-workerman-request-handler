<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit\Adapter;

use Blackfire\Client;
use Blackfire\Exception\LogicException;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\WorkermanRequestHandler\Adapter\BlackfireOnMessageAdapter;
use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

/**
 * @covers \Chubbyphp\WorkermanRequestHandler\Adapter\BlackfireOnMessageAdapter
 *
 * @internal
 */
final class BlackfireOnMessageAdapterTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvokeWithoutHeaderWithoutConfigAndWithoutLogger(): void
    {
        /** @var WorkermanTcpConnection|MockObject $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class);

        /** @var WorkermanRequest|MockObject $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class, [
            Call::create('header')->with('x-blackfire-query', null)->willReturn(null),
        ]);

        /** @var OnMessageInterface|MockObject $onMessage */
        $onMessage = $this->getMockByCalls(OnMessageInterface::class, [
            Call::create('__invoke')->with($workermanTcpConnection, $workermanRequest),
        ]);

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    public function testInvokeWithoutConfigAndWithoutLogger(): void
    {
        /** @var WorkermanTcpConnection|MockObject $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class);

        /** @var WorkermanRequest|MockObject $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class, [
            Call::create('header')->with('x-blackfire-query', null)->willReturn('workerman'),
        ]);

        /** @var OnMessageInterface|MockObject $onMessage */
        $onMessage = $this->getMockByCalls(OnMessageInterface::class, [
            Call::create('__invoke')->with($workermanTcpConnection, $workermanRequest),
        ]);

        /** @var Probe|MockObject $probe */
        $probe = $this->getMockByCalls(Probe::class);

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with(new ArgumentInstanceOf(Configuration::class), true)->willReturn($probe),
            Call::create('endProbe')->with($probe),
        ]);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    public function testInvokeWithConfigAndWithLogger(): void
    {
        /** @var WorkermanTcpConnection|MockObject $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class);

        /** @var WorkermanRequest|MockObject $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class, [
            Call::create('header')->with('x-blackfire-query', null)->willReturn('workerman'),
        ]);

        /** @var OnMessageInterface|MockObject $onMessage */
        $onMessage = $this->getMockByCalls(OnMessageInterface::class, [
            Call::create('__invoke')->with($workermanTcpConnection, $workermanRequest),
        ]);

        /** @var Configuration|MockObject $config */
        $config = $this->getMockByCalls(Configuration::class);

        /** @var Probe|MockObject $probe */
        $probe = $this->getMockByCalls(Probe::class);

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with($config, true)->willReturn($probe),
            Call::create('endProbe')->with($probe),
        ]);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockByCalls(LoggerInterface::class);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client, $config, $logger);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    public function testInvokeWithExceptionOnCreateProbe(): void
    {
        /** @var WorkermanTcpConnection|MockObject $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class);

        /** @var WorkermanRequest|MockObject $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class, [
            Call::create('header')->with('x-blackfire-query', null)->willReturn('workerman'),
        ]);

        /** @var OnMessageInterface|MockObject $onMessage */
        $onMessage = $this->getMockByCalls(OnMessageInterface::class, [
            Call::create('__invoke')->with($workermanTcpConnection, $workermanRequest),
        ]);

        /** @var Configuration|MockObject $config */
        $config = $this->getMockByCalls(Configuration::class);

        $exception = new LogicException('Something went wrong');

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with($config, true)->willThrowException($exception),
        ]);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockByCalls(LoggerInterface::class, [
            Call::create('error')->with('Blackfire exception: Something went wrong', []),
        ]);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client, $config, $logger);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    public function testInvokeWithExceptionOnProbeEnd(): void
    {
        /** @var WorkermanTcpConnection|MockObject $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class);

        /** @var WorkermanRequest|MockObject $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class, [
            Call::create('header')->with('x-blackfire-query', null)->willReturn('workerman'),
        ]);

        /** @var OnMessageInterface|MockObject $onMessage */
        $onMessage = $this->getMockByCalls(OnMessageInterface::class, [
            Call::create('__invoke')->with($workermanTcpConnection, $workermanRequest),
        ]);

        /** @var Configuration|MockObject $config */
        $config = $this->getMockByCalls(Configuration::class);

        /** @var Probe|MockObject $probe */
        $probe = $this->getMockByCalls(Probe::class);

        $exception = new LogicException('Something went wrong');

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with($config, true)->willReturn($probe),
            Call::create('endProbe')->with($probe)->willThrowException($exception),
        ]);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockByCalls(LoggerInterface::class, [
            Call::create('error')->with('Blackfire exception: Something went wrong', []),
        ]);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client, $config, $logger);
        $adapter($workermanTcpConnection, $workermanRequest);
    }
}
