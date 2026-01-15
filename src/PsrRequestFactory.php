<?php

declare(strict_types=1);

namespace Chubbyphp\WorkermanRequestHandler;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

final class PsrRequestFactory implements PsrRequestFactoryInterface
{
    public function __construct(
        private readonly ServerRequestFactoryInterface $serverRequestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly UploadedFileFactoryInterface $uploadedFileFactory
    ) {}

    public function create(WorkermanTcpConnection $workermanTcpConnection, WorkermanRequest $workermanRequest): ServerRequestInterface
    {
        $request = $this->serverRequestFactory->createServerRequest(
            $workermanRequest->method(),
            $workermanRequest->uri(),
            $this->createServerParams($workermanTcpConnection),
        );

        /** @var array<string, string> $headers */
        $headers = $workermanRequest->header();

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        /** @var array<string, string> $cookies */
        $cookies = $workermanRequest->cookie();

        $request = $request->withCookieParams($cookies);

        /** @var array<string, string> $queryParams */
        $queryParams = $workermanRequest->get() ?? [];

        /** @var null|array<string, mixed>|object $parsedBody */
        $parsedBody = $workermanRequest->post();

        /** @var array<string, array<string, mixed>> $uploadedFilesData */
        $uploadedFilesData = $workermanRequest->file() ?? [];

        $request = $request->withQueryParams($queryParams);
        $request = $request->withParsedBody($parsedBody);
        $request = $request->withUploadedFiles($this->uploadedFiles($uploadedFilesData));

        $request->getBody()->write($workermanRequest->rawBody());

        return $request;
    }

    /**
     * @return array<string, string>
     */
    private function createServerParams(WorkermanTcpConnection $workermanTcpConnection): array
    {
        return [
            'REMOTE_ADDR' => $workermanTcpConnection->getRemoteIp(),
            'REMOTE_PORT' => (string) $workermanTcpConnection->getRemotePort(),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $files
     *
     * @return array<string, mixed>
     */
    private function uploadedFiles(array $files): array
    {
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            if (isset($file['tmp_name'])) {
                /** @var array{tmp_name: string, size: null|int, error: int, name: null|string, type: null|string} $file */
                $uploadedFiles[$key] = $this->createUploadedFile($file);
            } else {
                /** @var array<string, array<string, mixed>> $file */
                $uploadedFiles[$key] = $this->uploadedFiles($file);
            }
        }

        return $uploadedFiles;
    }

    /**
     * @param array{tmp_name: string, size: null|int, error: int, name: null|string, type: null|string} $file
     */
    private function createUploadedFile(array $file): UploadedFileInterface
    {
        try {
            $stream = $this->streamFactory->createStreamFromFile($file['tmp_name']);
        } catch (\RuntimeException) {
            $stream = $this->streamFactory->createStream();
        }

        return $this->uploadedFileFactory->createUploadedFile(
            $stream,
            $file['size'],
            $file['error'],
            $file['name'],
            $file['type']
        );
    }
}
