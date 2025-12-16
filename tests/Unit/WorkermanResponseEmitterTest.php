<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\WorkermanRequestHandler\Unit;

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitter;
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
    public function testInvoke(): void
    {
        $builder = new MockObjectBuilder();

        $headers = [
            'Set-Cookies' => ['id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly; SameSite=strict'],
            'Content-Type' => ['application/json'],
        ];

        /** @var StreamInterface $responseBody */
        $responseBody = $builder->create(StreamInterface::class, [
            new WithReturn('__toString', [], 'This is the body.'),
        ]);

        /** @var ResponseInterface $response */
        $response = $builder->create(ResponseInterface::class, [
            new WithReturn('getStatusCode', [], 200),
            new WithReturn('getReasonPhrase', [], 'OK'),
            new WithReturn('getHeaders', [], $headers),
            new WithReturn('getBody', [], $responseBody),
        ]);

        /** @var WorkermanTcpConnection $workermanTcpConnection */
        $workermanTcpConnection = $builder->create(WorkermanTcpConnection::class, [
            new WithCallback('send', static function (WorkermanResponse $workermanResponse, $flag) use ($headers): void {
                self::assertSame(200, self::getWorkermanResponseProperty($workermanResponse, 'status'));
                self::assertSame('OK', self::getWorkermanResponseProperty($workermanResponse, 'reason'));
                self::assertSame($headers, self::getWorkermanResponseProperty($workermanResponse, 'headers'));
                self::assertSame('This is the body.', self::getWorkermanResponseProperty($workermanResponse, 'body'));
                self::assertFalse($flag);
            }),
        ]);

        $workermanResponseEmitter = new WorkermanResponseEmitter();
        $workermanResponseEmitter->emit($response, $workermanTcpConnection);
    }

    private static function getWorkermanResponseProperty(WorkermanResponse $workermanResponse, string $property)
    {
        $reflectionProperty = new \ReflectionProperty($workermanResponse, $property);

        return $reflectionProperty->getValue($workermanResponse);
    }
}
