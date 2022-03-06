<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\WorkermanRequestHandler\PsrRequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Workerman\Protocols\Http\Request as WorkermanRequest;

/**
 * @covers \Chubbyphp\WorkermanRequestHandler\PsrRequestFactory
 *
 * @internal
 */
final class PsrRequestFactoryTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvoke(): void
    {
        /** @var MockObject|WorkermanRequest $workermanRequest */
        $workermanRequest = $this->getMockByCalls(WorkermanRequest::class, [
            Call::create('method')->with()->willReturn('POST'),
            Call::create('uri')->with()->willReturn('/application'),
            Call::create('header')->with(null, null)->willReturn(['Content-Type' => 'multipart/form-data']),
            Call::create('cookie')->with(null, null)->willReturn(['PHPSESSID' => '537cd1fa-f6c1-41ee-85b2-1abcfd6eafb7']),
            Call::create('get')->with(null, null)->willReturn(['trackingId' => '82fa3d6a-3255-4716-8ea0-ed7bd19b7241']),
            Call::create('post')->with(null, null)->willReturn([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@gmail.com',
                'lastOccupation' => 'PHP Developer',
            ]),
            Call::create('file')->with(null)->willReturn([
                'cv' => [
                    'name' => 'CV.pdf',
                    'type' => 'application/pdf',
                    'tmp_name' => '/tmp/php9875842a',
                    'error' => 0,
                    'size' => 1_048_576,
                ],
                'certificates' => [
                    [
                        'name' => 'Advanced PHP 2017.pdf',
                        'type' => 'application/pdf',
                        'tmp_name' => '/tmp/php8d5f55ce',
                        'error' => 0,
                        'size' => 389120,
                    ],
                    [
                        'name' => 'Advanced Achitecture 2018.pdf',
                        'type' => 'application/pdf',
                        'tmp_name' => '/tmp/php123a6bf6',
                        'error' => 0,
                        'size' => 524288,
                    ],
                ],
            ]),
            Call::create('rawBody')->with()->willReturn('This is the body.'),
        ]);

        /** @var MockObject|StreamInterface $requestBody */
        $requestBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('write')->with('This is the body.'),
        ]);

        /** @var MockObject|StreamInterface $uploadedFileStream1 */
        $uploadedFileStream1 = $this->getMockByCalls(StreamInterface::class);

        /** @var MockObject|StreamInterface $uploadedFileStream2 */
        $uploadedFileStream2 = $this->getMockByCalls(StreamInterface::class);

        /** @var MockObject|StreamInterface $uploadedFileStream3 */
        $uploadedFileStream3 = $this->getMockByCalls(StreamInterface::class);

        $uploadedFileException = new \RuntimeException('test');

        /** @var MockObject|StreamFactoryInterface $streamFactory */
        $streamFactory = $this->getMockByCalls(StreamFactoryInterface::class, [
            Call::create('createStreamFromFile')->with('/tmp/php9875842a', 'r')->willReturn($uploadedFileStream1),
            Call::create('createStreamFromFile')->with('/tmp/php8d5f55ce', 'r')->willReturn($uploadedFileStream2),
            Call::create('createStreamFromFile')
                ->with('/tmp/php123a6bf6', 'r')
                ->willThrowException($uploadedFileException),
            Call::create('createStream')->with('')->willReturn($uploadedFileStream3),
        ]);

        /** @var MockObject|UploadedFileInterface $uploadedFile1 */
        $uploadedFile1 = $this->getMockByCalls(UploadedFileInterface::class);

        /** @var MockObject|UploadedFileInterface $uploadedFile2 */
        $uploadedFile2 = $this->getMockByCalls(UploadedFileInterface::class);

        /** @var MockObject|UploadedFileInterface $uploadedFile3 */
        $uploadedFile3 = $this->getMockByCalls(UploadedFileInterface::class);

        /** @var MockObject|UploadedFileFactoryInterface $uploadedFileFactory */
        $uploadedFileFactory = $this->getMockByCalls(UploadedFileFactoryInterface::class, [
            Call::create('createUploadedFile')
                ->with($uploadedFileStream1, 1_048_576, 0, 'CV.pdf', 'application/pdf')
                ->willReturn($uploadedFile1),
            Call::create('createUploadedFile')
                ->with($uploadedFileStream2, 389120, 0, 'Advanced PHP 2017.pdf', 'application/pdf')
                ->willReturn($uploadedFile2),
            Call::create('createUploadedFile')
                ->with($uploadedFileStream3, 524288, 0, 'Advanced Achitecture 2018.pdf', 'application/pdf')
                ->willReturn($uploadedFile3),
        ]);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('withHeader')->with('Content-Type', 'multipart/form-data')->willReturnSelf(),
            Call::create('withCookieParams')
                ->with(['PHPSESSID' => '537cd1fa-f6c1-41ee-85b2-1abcfd6eafb7'])
                ->willReturnSelf(),
            Call::create('withQueryParams')
                ->with(['trackingId' => '82fa3d6a-3255-4716-8ea0-ed7bd19b7241'])
                ->willReturnSelf(),
            Call::create('withParsedBody')
                ->with([
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'john.doe@gmail.com',
                    'lastOccupation' => 'PHP Developer',
                ])
                ->willReturnSelf(),
            Call::create('withUploadedFiles')
                ->with(new ArgumentCallback(
                    static function (array $uploadedFiles) use ($uploadedFile1, $uploadedFile2, $uploadedFile3): void {
                        self::assertArrayHasKey('cv', $uploadedFiles);

                        self::assertSame($uploadedFile1, $uploadedFiles['cv']);

                        self::assertArrayHasKey('certificates', $uploadedFiles);

                        self::assertCount(2, $uploadedFiles['certificates']);

                        self::assertSame($uploadedFile2, $uploadedFiles['certificates'][0]);
                        self::assertSame($uploadedFile3, $uploadedFiles['certificates'][1]);
                    }
                ))
                ->willReturnSelf(),
            Call::create('getBody')->with()->willReturn($requestBody),
        ]);

        /** @var MockObject|ServerRequestFactoryInterface $serverRequestFactory */
        $serverRequestFactory = $this->getMockByCalls(ServerRequestFactoryInterface::class, [
            Call::create('createServerRequest')
                ->with('POST', '/application', [])
                ->willReturn($request),
        ]);

        $psrRequestFactory = new PsrRequestFactory($serverRequestFactory, $streamFactory, $uploadedFileFactory);
        $psrRequestFactory->create($workermanRequest);
    }
}
