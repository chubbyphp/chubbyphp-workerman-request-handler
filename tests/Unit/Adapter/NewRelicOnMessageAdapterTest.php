<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler\Adapter
{
    final class TestNewRelicStartTransaction
    {
        /**
         * @var array<int, array>
         */
        private static array $calls = [];

        public static function add(string $appname, ?string $license = null): void
        {
            self::$calls[] = ['appname' => $appname, 'license' => $license];
        }

        /**
         * @return array<int, array>
         */
        public static function all(): array
        {
            return self::$calls;
        }

        public static function reset(): void
        {
            self::$calls = [];
        }
    }

    function newrelic_start_transaction(string $appname, ?string $license = null): void
    {
        TestNewRelicStartTransaction::add($appname, $license);
    }

    final class TestNewRelicEndTransaction
    {
        /**
         * @var array<int, array>
         */
        private static array $calls = [];

        public static function add(bool $ignore): void
        {
            self::$calls[] = ['ignore' => $ignore];
        }

        /**
         * @return array<int, array>
         */
        public static function all(): array
        {
            return self::$calls;
        }

        public static function reset(): void
        {
            self::$calls = [];
        }
    }

    function newrelic_end_transaction(bool $ignore = false): void
    {
        TestNewRelicEndTransaction::add($ignore);
    }
}

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit\Adapter
{
    use Chubbyphp\Mock\MockMethod\WithoutReturn;
    use Chubbyphp\Mock\MockObjectBuilder;
    use Chubbyphp\WorkermanRequestHandler\Adapter\NewRelicOnMessageAdapter;
    use Chubbyphp\WorkermanRequestHandler\Adapter\TestNewRelicEndTransaction;
    use Chubbyphp\WorkermanRequestHandler\Adapter\TestNewRelicStartTransaction;
    use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;
    use PHPUnit\Framework\TestCase;
    use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
    use Workerman\Protocols\Http\Request as WorkermanRequest;

    /**
     * @covers \Chubbyphp\WorkermanRequestHandler\Adapter\NewRelicOnMessageAdapter
     *
     * @internal
     */
    final class NewRelicOnMessageAdapterTest extends TestCase
    {
        public function testInvoke(): void
        {
            TestNewRelicStartTransaction::reset();
            TestNewRelicEndTransaction::reset();

            $builder = new MockObjectBuilder();

            /** @var WorkermanTcpConnection $workermanTcpConnection */
            $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, []);

            /** @var WorkermanRequest $workermanRequest */
            $workermanRequest = $builder->create(WorkermanRequest::class, []);

            /** @var OnMessageInterface $onMessage */
            $onMessage = $builder->create(OnMessageInterface::class, [
                new WithoutReturn('__invoke', [$workermanTcpConnection, $workermanRequest]),
            ]);

            $adapter = new NewRelicOnMessageAdapter($onMessage, 'myapp');
            $adapter($workermanTcpConnection, $workermanRequest);

            self::assertSame([['appname' => 'myapp', 'license' => null]], TestNewRelicStartTransaction::all());
            self::assertSame([['ignore' => false]], TestNewRelicEndTransaction::all());
        }
    }
}
