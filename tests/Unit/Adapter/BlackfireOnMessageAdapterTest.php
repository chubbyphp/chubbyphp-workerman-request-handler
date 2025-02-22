<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit\Adapter;

use Blackfire\Client;
use Blackfire\Exception\LogicException;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithException;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\WorkermanRequestHandler\Adapter\BlackfireOnMessageAdapter;
use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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
    #[DoesNotPerformAssertions]
    public function testInvokeWithoutHeaderWithoutConfigAndWithoutLogger(): void
    {
        $builder = new MockObjectBuilder();

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

        /** @var WorkermanRequest $workermanRequest */
        $workermanRequest = $builder->create(WorkermanRequest::class, [
            new WithReturn('header', ['x-blackfire-query', null], null),
        ]);

        /** @var OnMessageInterface $onMessage */
        $onMessage = $builder->create(OnMessageInterface::class, [
            new WithoutReturn('__invoke', [$workermanTcpConnection, $workermanRequest]),
        ]);

        /** @var Client $client */
        $client = $builder->create(Client::class, []);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    public function testInvokeWithoutConfigAndWithoutLogger(): void
    {
        $builder = new MockObjectBuilder();

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

        /** @var WorkermanRequest $workermanRequest */
        $workermanRequest = $builder->create(WorkermanRequest::class, [
            new WithReturn('header', ['x-blackfire-query', null], 'workerman'),
        ]);

        /** @var OnMessageInterface $onMessage */
        $onMessage = $builder->create(OnMessageInterface::class, [
            new WithoutReturn('__invoke', [$workermanTcpConnection, $workermanRequest]),
        ]);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithCallback('createProbe', static function (Configuration $configuration, bool $enabled) use ($probe): Probe {
                self::assertTrue($enabled);

                return $probe;
            }),
            new WithoutReturn('endProbe', [$probe]),
        ]);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithConfigAndWithLogger(): void
    {
        $builder = new MockObjectBuilder();

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

        /** @var WorkermanRequest $workermanRequest */
        $workermanRequest = $builder->create(WorkermanRequest::class, [
            new WithReturn('header', ['x-blackfire-query', null], 'workerman'),
        ]);

        /** @var OnMessageInterface $onMessage */
        $onMessage = $builder->create(OnMessageInterface::class, [
            new WithoutReturn('__invoke', [$workermanTcpConnection, $workermanRequest]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithReturn('createProbe', [$config, true], $probe),
            new WithoutReturn('endProbe', [$probe]),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, []);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client, $config, $logger);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithExceptionOnCreateProbe(): void
    {
        $builder = new MockObjectBuilder();

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

        /** @var WorkermanRequest $workermanRequest */
        $workermanRequest = $builder->create(WorkermanRequest::class, [
            new WithReturn('header', ['x-blackfire-query', null], 'workerman'),
        ]);

        /** @var OnMessageInterface $onMessage */
        $onMessage = $builder->create(OnMessageInterface::class, [
            new WithoutReturn('__invoke', [$workermanTcpConnection, $workermanRequest]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        $exception = new LogicException('Something went wrong');

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithException('createProbe', [$config, true], $exception),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, [
            new WithoutReturn('error', ['Blackfire exception: Something went wrong', []]),
        ]);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client, $config, $logger);
        $adapter($workermanTcpConnection, $workermanRequest);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithExceptionOnProbeEnd(): void
    {
        $builder = new MockObjectBuilder();

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

        /** @var WorkermanRequest $workermanRequest */
        $workermanRequest = $builder->create(WorkermanRequest::class, [
            new WithReturn('header', ['x-blackfire-query', null], 'workerman'),
        ]);

        /** @var OnMessageInterface $onMessage */
        $onMessage = $builder->create(OnMessageInterface::class, [
            new WithoutReturn('__invoke', [$workermanTcpConnection, $workermanRequest]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        $exception = new LogicException('Something went wrong');

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithReturn('createProbe', [$config, true], $probe),
            new WithException('endProbe', [$probe], $exception),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, [
            new WithoutReturn('error', ['Blackfire exception: Something went wrong', []]),
        ]);

        $adapter = new BlackfireOnMessageAdapter($onMessage, $client, $config, $logger);
        $adapter($workermanTcpConnection, $workermanRequest);
    }
}
