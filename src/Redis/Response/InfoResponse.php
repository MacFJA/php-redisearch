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

namespace MacFJA\RediSearch\Redis\Response;

use function assert;
use BadMethodCallException;
use function in_array;
use InvalidArgumentException;
use function is_array;
use function is_object;
use function is_string;
use MacFJA\RediSearch\Redis\Command\CreateCommand\CreateCommandFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Response;
use Stringable;

/**
 * @method string     getIndexName()
 * @method array      getIndexOptions()
 * @method array      getFields()
 * @method int        getNumDocs()
 * @method int        getMaxDocId()
 * @method int        getNumTerms()
 * @method int        getNumRecords()
 * @method float      getInvertedSzMb()
 * @method int        getTotalInvertedIndexBlocks()
 * @method float      getOffsetVectorsSzMb()
 * @method float      getDocTableSizeMb()
 * @method float      getSortableValuesSizeMb()
 * @method float      getKeyTableSizeMb()
 * @method null|float getRecordsPerDocAvg()
 * @method null|float getBytesPerRecordAvg()
 * @method null|float getOffsetsPerTermAvg()
 * @method null|float getOffsetBitsPerRecordAvg()
 * @method bool       getHashIndexingFailures()
 * @method bool       getIndexing()
 * @method float      getPercentIndexed()
 * @method array      getGcStats()
 * @method array      getCursorStats()
 * @method array      getStopwordsList()
 */
class InfoResponse implements Response
{
    use ArrayResponseTrait;

    private const TO_FLOAT = ['*_sz_mb', '*_size_mb', '*_avg', 'percent_indexed'];
    private const TO_INT = ['num_*', 'max_doc_id', 'total_inverted_index_blocks', 'hash_indexing_failures'];
    private const TO_STRING = ['index_name'];
    private const TO_BOOLEAN = ['indexing'];

    /**
     * @var array<array<mixed>|float|int|string>
     */
    private $rawLines;

    /**
     * @param array<array<mixed>|float|int|string> $rawLines
     */
    public function __construct(array $rawLines)
    {
        $this->rawLines = $rawLines;
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return null|float|int|mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (!(0 === strpos($name, 'get'))) {
            throw new BadMethodCallException();
        }
        $property = substr($name, 3);
        if (!($property[0] === strtoupper($property[0]))) {
            throw new BadMethodCallException();
        }
        $keyName = $this->pascalCaseToSnakeCase($property);
        $keys = self::getKeys($this->rawLines);
        if (!in_array($keyName, $keys, true)) {
            throw new InvalidArgumentException();
        }

        $value = self::getValue($this->rawLines, $keyName);
        if ('-nan' === $value || 'inf' === $value) {
            return null;
        }

        return $this->cast($value, $keyName);
    }

    /**
     * @return array<CreateCommandFieldOption>
     */
    public function getFieldsAsOption(): array
    {
        $fields = self::getValue($this->rawLines, 'fields');
        assert(is_array($fields));

        return array_map(static function (array $field): CreateCommandFieldOption {
            $fieldName = (string) array_shift($field);
            array_walk($field, static function (&$item) {
                if (is_object($item) && ($item instanceof Stringable || method_exists($item, '__toString'))) {
                    $item = (string) $item;
                }

                return $item;
            });

            switch (self::getValue($field, 'type')) {
                case 'GEO':
                    $object = new GeoFieldOption();

                    break;

                case 'NUMERIC':
                    $object = new NumericFieldOption();

                    break;

                case 'TAG':
                    $object = new TagFieldOption();
                    $object = self::setOptionValue($object, $field, 'SEPARATOR', 'separator');

                    break;

                case 'TEXT':
                default:
                    $object = new TextFieldOption();
                    $object = self::setOptionValue($object, $field, 'WEIGHT', 'weight');
                    $object = self::setOptionValue($object, $field, 'PHONETIC', 'phonetic');
                    $object = self::setOptionFlag($object, $field, 'NOSTEM', 'no_stem');
            }

            assert($object instanceof GroupedOption);
            $object->setDataOfOption('field', $fieldName);
            $object = self::setOptionFlag($object, $field, 'NOINDEX', 'no_index');
            $object = self::setOptionFlag($object, $field, 'SORTABLE', 'sortable');

            return $object;
        }, $fields);
    }

    /**
     * @return null|array<string,mixed>|mixed
     */
    public function getIndexDefinition(?string $key = null)
    {
        $rawDefinitions = self::getValue($this->rawLines, 'index_definition');
        assert(is_array($rawDefinitions));
        $data = self::getPairs($rawDefinitions);
        if (!is_string($key)) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    private function pascalCaseToSnakeCase(string $pascalCase): string
    {
        return strtolower(preg_replace('/([^A-Z\s])([A-Z])/', '$1_$2', $pascalCase) ?? $pascalCase);
    }

    /**
     * @param mixed $value
     *
     * @return float|int|mixed
     */
    private function cast($value, string $key)
    {
        foreach (self::TO_FLOAT as $floatKey) {
            if (fnmatch($floatKey, $key)) {
                return (float) $value;
            }
        }
        foreach (self::TO_INT as $intKey) {
            if (fnmatch($intKey, $key)) {
                return (int) $value;
            }
        }
        foreach (self::TO_STRING as $intKey) {
            if (fnmatch($intKey, $key)) {
                return (string) $value;
            }
        }
        foreach (self::TO_BOOLEAN as $intKey) {
            if (fnmatch($intKey, $key)) {
                return '1' === (string) $value;
            }
        }

        return $value;
    }

    /**
     * @param array<float|int|string> $fields
     */
    private static function setOptionValue(CreateCommandFieldOption $commandOption, array $fields, string $key, string $optionName): CreateCommandFieldOption
    {
        assert($commandOption instanceof TextFieldOption || $commandOption instanceof TagFieldOption);
        if (in_array($key, $fields, true)) {
            $commandOption->setDataOfOption($optionName, self::getNextValue($fields, $key));
        }

        return $commandOption;
    }

    /**
     * @param array<float|int|string> $fields
     */
    private static function setOptionFlag(CreateCommandFieldOption $commandOption, array $fields, string $key, string $optionName): CreateCommandFieldOption
    {
        assert($commandOption instanceof GroupedOption);
        if (in_array($key, $fields, true)) {
            $commandOption->setDataOfOption($optionName, true);
        }

        return $commandOption;
    }
}
