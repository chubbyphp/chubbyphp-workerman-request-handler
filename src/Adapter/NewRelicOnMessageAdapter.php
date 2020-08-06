<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler\Adapter;

use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

final class NewRelicOnMessageAdapter implements OnMessageInterface
{
    /**
     * @var OnMessageInterface
     */
    private $onRequest;

    /**
     * @var string
     */
    private $appname;

    public function __construct(OnMessageInterface $onRequest, string $appname)
    {
        $this->onRequest = $onRequest;
        $this->appname = $appname;
    }

    public function __invoke(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): void
    {
        newrelic_start_transaction($this->appname);

        $this->onRequest->__invoke($workermanTcpConnection, $workermanRequest);

        newrelic_end_transaction();
    }
}
