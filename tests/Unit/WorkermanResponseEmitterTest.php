<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Response as WorkermanResponse;

/**
 * @covers \Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitter
 *
 * @internal
 */
final class WorkermanResponseEmitterTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvoke(): void
    {
        $headers = [
            'Set-Cookies' => ['id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly; SameSite=strict'],
            'Content-Type' => ['application/json'],
        ];

        /** @var MockObject|StreamInterface $responseBody */
        $responseBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('__toString')->with()->willReturn('This is the body.'),
        ]);

        /** @var MockObject|ResponseInterface $response */
        $response = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getStatusCode')->with()->willReturn(200),
            Call::create('getReasonPhrase')->with()->willReturn('OK'),
            Call::create('getHeaders')->with()->willReturn($headers),
            Call::create('getBody')->with()->willReturn($responseBody),
        ]);

        /** @var MockObject|WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $this->getMockByCalls(WorkermanTcpConnection::class, [
            Call::create('send')
                ->with(
                    new ArgumentCallback(function (WorkermanResponse $workermanResponse) use ($headers): void {
                        self::assertSame(200, $this->getWorkermanResponseProperty($workermanResponse, '_status'));
                        self::assertSame('OK', $this->getWorkermanResponseProperty($workermanResponse, '_reason'));
                        self::assertSame($headers, $this->getWorkermanResponseProperty($workermanResponse, '_header'));
                        self::assertSame('This is the body.', $this->getWorkermanResponseProperty($workermanResponse, '_body'));
                    }),
                    false
                ),
        ]);

        $workermanResponseEmitter = new WorkermanResponseEmitter();
        $workermanResponseEmitter->emit($response, $workermanTcpConnection);
    }

    private function getWorkermanResponseProperty(WorkermanResponse $workermanResponse, string $property)
    {
        $reflectionProperty = new \ReflectionProperty($workermanResponse, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($workermanResponse);
    }
}
