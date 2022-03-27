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

namespace MacFJA\RediSearch\tests\integration;

use Amp\Redis\Config;
use Amp\Redis\RemoteExecutor;
use Closure;
use Credis_Client;
use function get_class;
use function is_string;
use MacFJA\RediSearch\Index;
use MacFJA\RediSearch\IndexBuilder;
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Query\Builder\Fuzzy;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\Redis\Command\Aggregate;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\GroupByOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption;
use MacFJA\RediSearch\Redis\Command\DropIndex;
use MacFJA\RediSearch\Redis\Command\IndexList;
use MacFJA\RediSearch\Redis\Command\Search;
use MacFJA\RediSearch\Redis\Command\SynDump;
use MacFJA\RediSearch\Redis\Command\SynUpdate;
use MacFJA\RediSearch\Redis\Response\PaginatedResponse;
use MacFJA\RediSearch\Redis\Response\SearchResponseItem;
use PHPUnit\Framework\TestCase;
use Rediska;
use TinyRedisClient;

/**
 * @covers \MacFJA\RediSearch\Index
 * @covers \MacFJA\RediSearch\IndexBuilder
 * @covers \MacFJA\RediSearch\Query\Builder
 * @covers \MacFJA\RediSearch\Redis\Client\AbstractClient
 * @covers \MacFJA\RediSearch\Redis\Client\AmpRedisClient
 * @covers \MacFJA\RediSearch\Redis\Client\CheprasovRedisClient
 * @covers \MacFJA\RediSearch\Redis\Client\ClientFacade
 * @covers \MacFJA\RediSearch\Redis\Client\CredisClient
 * @covers \MacFJA\RediSearch\Redis\Client\PredisClient
 * @covers \MacFJA\RediSearch\Redis\Client\RedisentClient
 * @covers \MacFJA\RediSearch\Redis\Client\RediskaClient
 * @covers \MacFJA\RediSearch\Redis\Client\TinyRedisClient
 * @covers \MacFJA\RediSearch\Redis\Command\Aggregate
 * @covers \MacFJA\RediSearch\Redis\Command\DropIndex
 * @covers \MacFJA\RediSearch\Redis\Command\IndexList
 * @covers \MacFJA\RediSearch\Redis\Command\Info
 * @covers \MacFJA\RediSearch\Redis\Command\Search
 * @covers \MacFJA\RediSearch\Redis\Command\SynDump
 * @covers \MacFJA\RediSearch\Redis\Command\SynUpdate
 * @covers \MacFJA\RediSearch\Redis\Initializer
 * @covers \MacFJA\RediSearch\Redis\Response\InfoResponse
 * @covers \MacFJA\RediSearch\Redis\Response\PaginatedResponse
 *
 * @uses \MacFJA\RediSearch\Query\Builder\AbstractGroup
 * @uses \MacFJA\RediSearch\Query\Builder\AndGroup
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @uses \MacFJA\RediSearch\Redis\Command\AddFieldOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\GroupByOption
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\ReduceOption
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\WithCursor
 * @uses \MacFJA\RediSearch\Redis\Command\Create
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\BaseCreateFieldOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NumberedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait
 * @uses \MacFJA\RediSearch\Redis\Command\PrefixFieldName
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\HighlightOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SortByOption
 * @uses \MacFJA\RediSearch\Redis\Command\SearchCommand\SummarizeOption
 * @uses \MacFJA\RediSearch\Redis\Command\AggregateCommand\SortByOption
 * @uses \MacFJA\RediSearch\Query\Builder\Fuzzy
 * @uses \MacFJA\RediSearch\Query\Escaper
 *
 * @group integration
 *
 * @internal
 */
class DockerTest extends TestCase
{
    /** @var bool */
    private static $skip = false;

    /** @var string */
    private static $containerCommand = 'podman';

    /** @var null|string */
    private static $containerId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        exec('which podman', $output, $code);
        if ($code > 0) {
            exec('which docker', $output, $code);
            self::$containerCommand = 'docker';
        }
        if ($code > 0) {
            self::$skip = true;

            return;
        }
        $output = [];
        exec(self::$containerCommand.' run --rm -p 16379:6379 -d redislabs/redisearch:latest', $output, $code);
        self::$containerId = reset($output) ?: null;
        Client\AbstractClient::$disableNotice = true;
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        if (self::$skip || !is_string(self::$containerId)) {
            return;
        }
        exec(self::$containerCommand.' stop '.escapeshellarg(self::$containerId), $output);
        Client\AbstractClient::$disableNotice = false;
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (self::$skip) {
            static::markTestSkipped('Podman/Docker is missing');
        }
    }

    /**
     * @param mixed $redisBuilder
     *
     * @medium
     *
     * @dataProvider dataProvider
     */
    public function testGetClient(string $expectedClass, $redisBuilder): void
    {
        $facade = new ClientFacade();
        $client = $facade->getClient($redisBuilder());

        static::assertSame($expectedClass, get_class($client));
    }

    /**
     * @dataProvider dataProvider
     * @large
     *
     * @param mixed $clientBuilder
     */
    public function testIntegration($clientBuilder): void
    {
        $client = $clientBuilder();

        $list = $client->execute(new IndexList());
        static::assertEmpty($list);
        $builder = new IndexBuilder();
        $builder
            ->withIndex('testDoc')
            ->addTextField('lastname', false, null, null, true)
            ->addTextField('firstname')
            ->addNumericField('age')
            ->create($client)
        ;
        static::assertEquals(['testDoc'], $client->execute(new IndexList()));

        $index = new Index('testDoc', $client);
        $docToRemove = [];
        $docToRemove[] = $index->addDocumentFromArray(['firstname' => 'Joe', 'lastname' => 'Doe', 'age' => 30]);
        $docToRemove[] = $index->addDocumentFromArray(['firstname' => 'Joe', 'age' => 30]);
        $docToRemove[] = $index->addDocumentFromArray(['firstname' => 'Joe', 'age' => 30, 'fullname' => 'eeeee']);

        $search = new Search();

        $query = (new Builder())
            ->addElement(new Fuzzy('Joo'))
        ;

        $search
            ->setIndex('testDoc')
            ->setQuery($query->render())
            ->setWithPayloads()
            ->setHighlight(['lastname'], '<b>', '</b>')
            ->setWithScores()
        ;

        /** @var PaginatedResponse $result */
        $result = $client->execute($search);
        static::assertCount(1, $result);
        static::assertSame(1, $result->getPageCount());
        static::assertSame(3, $result->getTotalCount());

        /** @var array<SearchResponseItem> $page */
        foreach ($result as $page) {
            foreach ($page as $item) {
                static::assertSame(30, (int) $item->getFieldValue('age'));
            }
        }

        $client->execute((new SynUpdate())->setIndex('testDoc')->setGroupId('Joe')->setTerms('John'));
        $syns = $client->execute((new SynDump())->setIndex('testDoc'));
        static::assertEquals([
            'john', ['Joe'],
        ], $syns);

        $result = $client->pipeline(
            (new Search())
                ->setIndex('testDoc')
                ->setQuery('%%Joe%%')
                ->setWithPayloads()
                ->setWithScores()
                ->setHighlight(['lastname'], '<b>', '</b>'),
            (new Aggregate())
                ->setIndex('testDoc')
                ->setQuery('*')
                ->addGroupBy(new GroupByOption(['lastname'], [ReduceOption::count('count')])),
            (new Aggregate())
                ->setIndex('testDoc')
                ->setQuery('*')
                ->addGroupBy(new GroupByOption(['firstname'], [ReduceOption::count('count')])),
            (new Aggregate())
                ->setIndex('testDoc')
                ->setQuery('*')
                ->addGroupBy(new GroupByOption(['age'], [ReduceOption::count('count')])),
            (new Aggregate())
                ->setIndex('testDoc')
                ->setQuery('*')
                ->addGroupBy(new GroupByOption([], [ReduceOption::toList('age', 'list')]))
        );

        static::assertCount(1, $result[0]);
        static::assertSame(3, $result[0]->getTotalCount());
        static::assertEquals('1', current($result[1])[0]->getFieldValue('count'));
        static::assertEquals('2', current($result[1])[1]->getFieldValue('count'));
        static::assertEquals('3', current($result[2])[0]->getFieldValue('count'));
        static::assertEquals('3', current($result[3])[0]->getFieldValue('count'));
        static::assertEquals(['30'], current($result[4])[0]->getFieldValue('list'));

        $client->execute((new DropIndex())->setIndex('testDoc')->setDeleteDocument());
        $client->executeRaw('del', ...$docToRemove);
    }

    /**
     * @return array<array<Closure|string>>
     */
    public function dataProvider(string $testName): array
    {
        if ('testIntegration' === $testName) {
            return [
                [static function () { return Client\PredisClient::make(new \Predis\Client(['scheme' => 'tcp', 'host' => 'localhost', 'port' => '16379', 'db' => 0])); }],
                [static function () { return Client\RediskaClient::make(new Rediska(['servers' => [['host' => 'localhost', 'port' => '16379', 'db' => 0]]])); }],
                [static function () { return Client\RedisentClient::make(new \redisent\Redis('redis://localhost:16379')); }],
                [static function () { return Client\CheprasovRedisClient::make(new \RedisClient\Client\Version\RedisClient6x0(['server' => 'localhost:16379', 'database' => 0])); }],
                [static function () { return Client\AmpRedisClient::make(new \Amp\Redis\Redis(new RemoteExecutor(Config::fromUri('redis://localhost:16379')))); }],
                [static function () { return Client\TinyRedisClient::make(new TinyRedisClient('localhost:16379')); }],
                [static function () { return Client\CredisClient::make(new Credis_Client('localhost', 16379, null, '', 0)); }],
            ];
        }

        return [
            [Client\PredisClient::class, static function () { return new \Predis\Client(['scheme' => 'tcp', 'host' => 'localhost', 'port' => '16379', 'db' => 0]); }],
            [Client\RediskaClient::class, static function () { return new Rediska(['servers' => [['host' => 'localhost', 'port' => '16379', 'db' => 0]]]); }],
            [Client\RedisentClient::class, static function () { return new \redisent\Redis('redis://localhost:16379'); }],
            [Client\CheprasovRedisClient::class, static function () { return new \RedisClient\Client\Version\RedisClient6x0(['server' => 'localhost:16379', 'database' => 0]); }],
            [Client\AmpRedisClient::class, static function () { return new \Amp\Redis\Redis(new RemoteExecutor(Config::fromUri('redis://localhost:16379'))); }],
            [Client\TinyRedisClient::class, static function () { return new TinyRedisClient('localhost:16379'); }],
            [Client\CredisClient::class, static function () { return new Credis_Client('localhost', 16379, null, '', 0); }],
        ];
    }
}
