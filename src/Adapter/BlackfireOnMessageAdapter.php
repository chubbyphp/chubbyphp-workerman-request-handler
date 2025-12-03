<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler\Adapter;

use Blackfire\Client;
use Blackfire\Exception\ExceptionInterface;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\WorkermanRequestHandler\OnMessageInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

final class BlackfireOnMessageAdapter implements OnMessageInterface
{
    public function __construct(
        private OnMessageInterface $onRequest,
        private Client $client,
        private Configuration $config = new Configuration(),
        private LoggerInterface $logger = new NullLogger()
    ) {}

    public function __invoke(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): void
    {
        if (null === $workermanRequest->header('x-blackfire-query')) {
            $this->onRequest->__invoke($workermanTcpConnection, $workermanRequest);

            return;
        }

        $probe = $this->startProbe();

        $this->onRequest->__invoke($workermanTcpConnection, $workermanRequest);

        if (!$probe instanceof Probe) {
            return;
        }

        $this->endProbe($probe);
    }

    private function startProbe(): ?Probe
    {
        try {
            return $this->client->createProbe($this->config);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(\sprintf('Blackfire exception: %s', $exception->getMessage()));
        }

        return null;
    }

    private function endProbe(Probe $probe): void
    {
        try {
            $this->client->endProbe($probe);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(\sprintf('Blackfire exception: %s', $exception->getMessage()));
        }
    }
}
