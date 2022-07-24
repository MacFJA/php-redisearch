<?php

declare(strict_types=1);

/*
 * Copyright MacFJA
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MacFJA\RediSearch\tests\Redis\Response;

use function array_slice;

use MacFJA\RediSearch\Exception\MissingClientException;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\PaginatedCommand;
use MacFJA\RediSearch\Redis\Response\PaginatedResponse;
use MacFJA\RediSearch\Redis\Response\SearchResponseItem;
use MacFJA\RediSearch\tests\fixtures\Redis\Command\FakePaginatedCommand;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Exception\MissingClientException
 * @covers \MacFJA\RediSearch\Redis\Response\PaginatedResponse
 *
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 *
 * @internal
 */
class PaginatedResponseTest extends TestCase
{
    public function testMultiplePages(): void
    {
        [$client, $command] = $this->getClientAndCommand();

        /** @var PaginatedResponse $response */
        $response = $client->execute($command);

        static::assertInstanceOf(PaginatedResponse::class, $response);

        static::assertTrue($response->valid());
        static::assertSame('foo', $response->current()[0]->getFieldValue('f1'));
        static::assertCount(4, $response);
        static::assertEquals(2, $response->getPageSize());
        static::assertEquals(4, $response->getPageCount());
        static::assertEquals(7, $response->getTotalCount());
        static::assertEquals(0, $response->key());

        $response->setClient($client);

        $response->next();

        static::assertTrue($response->valid());
        static::assertSame('bob', $response->current()[0]->getFieldValue('f1'));
        static::assertCount(4, $response);
        static::assertEquals(2, $response->getPageSize());
        static::assertEquals(4, $response->getPageCount());
        static::assertEquals(7, $response->getTotalCount());
        static::assertEquals(1, $response->key());

        $response->next();

        static::assertTrue($response->valid());
        static::assertSame('lorem', $response->current()[0]->getFieldValue('f1'));
        static::assertCount(4, $response);
        static::assertEquals(2, $response->getPageSize());
        static::assertEquals(4, $response->getPageCount());
        static::assertEquals(7, $response->getTotalCount());
        static::assertEquals(2, $response->key());

        $response->next();

        static::assertTrue($response->valid());
        static::assertSame('odd', $response->current()[0]->getFieldValue('f1'));
        static::assertCount(4, $response);
        static::assertEquals(2, $response->getPageSize());
        static::assertEquals(4, $response->getPageCount());
        static::assertEquals(7, $response->getTotalCount());
        static::assertEquals(3, $response->key());

        $response->next();

        static::assertFalse($response->valid());
    }

    public function testMultiplePagesIterator(): void
    {
        [$client, $command] = $this->getClientAndCommand();

        /** @var PaginatedResponse $response */
        $response = $client->execute($command);

        static::assertInstanceOf(PaginatedResponse::class, $response);
        $response->setClient($client);

        foreach ($response as $index => $page) {
            if (0 === $index) {
                static::assertSame('foo', $response->current()[0]->getFieldValue('f1'));
            }
            if (1 === $index) {
                static::assertSame('bob', $response->current()[0]->getFieldValue('f1'));
            }
            if (2 === $index) {
                static::assertSame('lorem', $response->current()[0]->getFieldValue('f1'));
            }
            if (3 === $index) {
                static::assertSame('odd', $response->current()[0]->getFieldValue('f1'));
            }

            static::assertEquals(2, $response->getPageSize());
            static::assertEquals(4, $response->getPageCount());
            static::assertEquals(7, $response->getTotalCount());
        }
    }

    public function testMissingClient(): void
    {
        $this->expectException(MissingClientException::class);
        [$client, $command] = $this->getClientAndCommand();

        /** @var PaginatedResponse $response */
        $response = $client->execute($command);

        static::assertInstanceOf(PaginatedResponse::class, $response);

        static::assertTrue($response->valid());

        $response->next();
        $response->current();
    }

    public function testPageSizeSetToZero(): void
    {
        $command = new FakePaginatedCommand([]);
        $command->setLimit(0, 0);
        $response = new PaginatedResponse($command, 2, []);

        static::assertSame(0, $response->key());
        static::assertSame(0, $response->getPageCount());
        static::assertCount(0, $response);
    }

    /**
     * @return array{0:Client,1:FakePaginatedCommand}
     */
    private function getClientAndCommand(): array
    {
        $command = new FakePaginatedCommand([]);

        $client = $this->createMock(Client::class);
        $client->method('execute')->willReturnOnConsecutiveCalls(
            self::getResponses($command, 0, 2),
            self::getResponses($command, 2, 4),
            self::getResponses($command, 4, 6),
            self::getResponses($command, 6, 7)
        );

        return [$client, $command];
    }

    private static function getResponses(PaginatedCommand $command, int $from, int $to): PaginatedResponse
    {
        $response = [
            new SearchResponseItem('foobar1', ['f1' => 'foo', 'f2' => 'bar']),
            new SearchResponseItem('foobar2', ['f1' => 'baz', 'f2' => 'alice']),
            new SearchResponseItem('foobar3', ['f1' => 'bob', 'f2' => 'charlie']),
            new SearchResponseItem('foobar4', ['f1' => 'john', 'f2' => 'doe']),
            new SearchResponseItem('foobar5', ['f1' => 'lorem', 'f2' => 'ipsum']),
            new SearchResponseItem('foobar6', ['f1' => 'foobar', 'f2' => 'foobaz']),
            new SearchResponseItem('foobar7', ['f1' => 'odd', 'f2' => 'page']),
        ];

        return new PaginatedResponse(
            $command,
            7,
            array_slice($response, $from, $to - $from, false)
        );
    }
}
