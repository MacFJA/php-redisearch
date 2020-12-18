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

namespace MacFJA\RedisSearch\Index;

use function array_map;
use function array_shift;
use function in_array;
use MacFJA\RedisSearch\Helper\DataHelper;
use MacFJA\RedisSearch\Helper\RedisHelper;
use MacFJA\RedisSearch\Index\Builder\Field;
use MacFJA\RedisSearch\Index\Builder\GeoField;
use MacFJA\RedisSearch\Index\Builder\NumericField;
use MacFJA\RedisSearch\Index\Builder\TagField;
use MacFJA\RedisSearch\Index\Builder\TextField;
use function sprintf;
use UnexpectedValueException;

class InfoResult
{
    /** @var int */
    protected $maxDocumentId;

    /** @var string */
    private $indexName;

    /** @var array<string> */
    private $indexOptions = [];

    /** @var array<string> */
    private $definition = [];

    /** @var array<mixed> */
    private $fields = [];

    /** @var int */
    private $documentCount;

    /** @var int */
    private $termsCount;

    /** @var float */
    private $averageRecordSize;

    /** @var float */
    private $indexSize;

    /** @var int */
    private $indexBlockCount;

    /** @var bool */
    private $indexing;

    /** @var int */
    private $indexingFailureCount;

    /** @var float */
    private $percentIndexed;

    /** @var array<string> */
    private $stopWords;

    /**
     * InfoResult constructor.
     *
     * @param array<string> $indexOptions
     * @param array<string> $definition
     * @param array<mixed>  $fields
     * @param array<string> $stopWords
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(string $indexName, array $indexOptions, array $definition, array $fields, int $documentCount, int $maxDocumentId, int $termsCount, float $averageRecordSize, float $indexSize, int $indexBlockCount, bool $indexing, int $indexingFailureCount, float $percentIndexed, array $stopWords)
    {
        DataHelper::assertArrayOf($indexOptions, 'string');
        DataHelper::assertArrayOf($definition, 'string');
        DataHelper::assertArrayOf($stopWords, 'string');
        $this->indexName = $indexName;
        $this->indexOptions = $indexOptions;
        $this->definition = $definition;
        $this->fields = $fields;
        $this->documentCount = $documentCount;
        $this->maxDocumentId = $maxDocumentId;
        $this->termsCount = $termsCount;
        $this->averageRecordSize = $averageRecordSize;
        $this->indexSize = $indexSize;
        $this->indexBlockCount = $indexBlockCount;
        $this->indexing = $indexing;
        $this->indexingFailureCount = $indexingFailureCount;
        $this->percentIndexed = $percentIndexed;
        $this->stopWords = $stopWords;
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return array<string>
     */
    public function getIndexOptions(): array
    {
        return $this->indexOptions;
    }

    /**
     * @return array<string>
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @return array<mixed>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<Field>
     */
    public function getFieldsAsObject(): array
    {
        return array_map(function (array $field): Field {
            $name = (string) array_shift($field);
            array_shift($field); // type
            $type = (string) array_shift($field);

            switch ($type) {
                case 'GEO':
                    return new GeoField(
                        $name,
                        in_array('NOINDEX', $field, true)
                    );
                case 'NUMERIC':
                    return new NumericField(
                        $name,
                        in_array('SORTABLE', $field, true),
                        in_array('NOINDEX', $field, true)
                    );
                case 'TAG':
                    return new TagField(
                        $name,
                        RedisHelper::getValue($field, 'SEPARATOR'),
                        in_array('SORTABLE', $field, true),
                        in_array('NOINDEX', $field, true)
                    );
                case 'TEXT':
                    return new TextField(
                        $name,
                        in_array('NOSTEM', $field, true),
                        /** @phan-suppress-next-line PhanPartialTypeMismatchArgument */
                        DataHelper::nullOrCast(RedisHelper::getValue($field, 'WEIGHT'), 'float'),
                        RedisHelper::getValue($field, 'PHONETIC'),
                        in_array('SORTABLE', $field, true),
                        in_array('NOINDEX', $field, true)
                    );
                default:
                    throw new UnexpectedValueException(sprintf('Unknown field type "%s" for field "%s"', $type, $name));
            }
        }, $this->fields);
    }

    public function getDocumentCount(): int
    {
        return $this->documentCount;
    }

    public function getMaxDocumentId(): int
    {
        return $this->maxDocumentId;
    }

    public function getTermsCount(): int
    {
        return $this->termsCount;
    }

    public function getAverageRecordSize(): float
    {
        return $this->averageRecordSize;
    }

    public function getIndexSize(): float
    {
        return $this->indexSize;
    }

    public function getIndexBlockCount(): int
    {
        return $this->indexBlockCount;
    }

    public function isIndexing(): bool
    {
        return $this->indexing;
    }

    public function getIndexingFailureCount(): int
    {
        return $this->indexingFailureCount;
    }

    public function getPercentIndexed(): float
    {
        return $this->percentIndexed;
    }

    /**
     * @return array<string>
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }
}
