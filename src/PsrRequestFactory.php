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
        $request = $request->withQueryParams($workermanRequest->get());
        $request = $request->withParsedBody($workermanRequest->post());
        $request = $request->withUploadedFiles($this->uploadedFiles($workermanRequest->file()));

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
     * @param array<string, array<string, int|string>> $files
     *
     * @return array<string, UploadedFileInterface>
     */
    private function uploadedFiles(array $files): array
    {
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            $uploadedFiles[$key] = isset($file['tmp_name']) ? $this->createUploadedFile($file) : $this->uploadedFiles($file);
        }

        return $uploadedFiles;
    }

    /**
     * @param array<string, int|string> $file
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
