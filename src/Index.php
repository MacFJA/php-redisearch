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

namespace MacFJA\RediSearch;

use function count;
use function is_array;
use function is_string;

use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command\AbstractCommand;
use MacFJA\RediSearch\Redis\Command\AliasAdd;
use MacFJA\RediSearch\Redis\Command\AliasDel;
use MacFJA\RediSearch\Redis\Command\AliasUpdate;
use MacFJA\RediSearch\Redis\Command\Alter;
use MacFJA\RediSearch\Redis\Command\CreateCommand\CreateCommandFieldOption;
use MacFJA\RediSearch\Redis\Command\DropIndex;
use MacFJA\RediSearch\Redis\Command\Info;
use MacFJA\RediSearch\Redis\Command\TagVals;
use MacFJA\RediSearch\Redis\Initializer;
use MacFJA\RediSearch\Redis\Response\InfoResponse;

class Index
{
    /** @var Client */
    private $client;

    /** @var null|InfoResponse */
    private $info;

    /** @var string */
    private $index;

    /** @var null|string */
    private $version;

    public function __construct(string $index, Client $client, ?string $version = null)
    {
        $this->client = $client;
        $this->index = $index;
        $this->version = $version;
    }

    /**
     * @param array<string,float|int|string> $properties
     */
    public function addDocumentFromArray(array $properties, ?string $hash = null): string
    {
        $prefixes = $this->getInfo()->getIndexDefinition('prefixes');
        $prefix = '';
        if (is_array($prefixes) && count($prefixes) > 0) {
            $prefix = (string) reset($prefixes);
        }
        $documentId = is_string($hash) ? $hash : uniqid($prefix, true);
        $query = [$documentId];
        foreach ($properties as $name => $value) {
            $query[] = $name;
            $query[] = $value;
        }

        $this->client->executeRaw('hset', ...$query);

        return $documentId;
    }

    public function deleteDocument(string $hash): bool
    {
        $count = $this->client->executeRaw('del', $hash);

        return 1 === $count;
    }

    public function addField(CreateCommandFieldOption $field): bool
    {
        $command = new Alter($this->getVersion());
        $command
            ->setIndex($this->index)
            ->addField($field)
        ;

        return 'OK' === (string) $this->client->execute($command);
    }

    public function delete(bool $withDocuments = false): bool
    {
        $command = new DropIndex($this->getVersion());
        $command->setIndex($this->index)
            ->setDeleteDocument($withDocuments)
        ;

        return 'OK' === (string) $this->client->execute($command);
    }

    public function addAlias(string $alias): bool
    {
        return 'OK' === (string) $this->client->execute(
            (new AliasAdd($this->getVersion()))
                ->setIndex($this->index)
                ->setAlias($alias)
        );
    }

    public function updateAlias(string $alias): bool
    {
        return 'OK' === (string) $this->client->execute((new AliasUpdate($this->getVersion()))->setIndex($this->index)->setAlias($alias));
    }

    public function deleteAlias(string $alias): bool
    {
        return 'OK' === (string) $this->client->execute((new AliasDel($this->getVersion()))->setAlias($alias));
    }

    /**
     * @return array<string>
     */
    public function getTagValues(string $fieldName): array
    {
        return $this->client->execute((new TagVals($this->getVersion()))->setIndex($this->index)->setField($fieldName));
    }

    public function getInfo(): InfoResponse
    {
        if (!($this->info instanceof InfoResponse)) {
            $this->info = $this->client->execute((new Info($this->getVersion()))->setIndex($this->index));
        }

        return $this->info;
    }

    private function getVersion(): string
    {
        if (!is_string($this->version)) {
            $this->version = Initializer::getRediSearchVersion($this->client) ?? AbstractCommand::MIN_IMPLEMENTED_VERSION;
        }

        return $this->version;
    }
}
