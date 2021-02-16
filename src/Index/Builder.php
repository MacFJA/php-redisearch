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

namespace MacFJA\RediSearch\Index;

use function array_filter;
use function array_merge;
use function assert;
use function count;
use function in_array;
use function is_string;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;
use MacFJA\RediSearch\Index\Builder\Exception\MissingNameException;
use MacFJA\RediSearch\Index\Builder\Exception\TooManyFields;
use MacFJA\RediSearch\Index\Builder\Exception\UnsupportedLanguageException;
use MacFJA\RediSearch\Index\Builder\Field;
use MacFJA\RediSearch\Index\Builder\GeoField;
use MacFJA\RediSearch\Index\Builder\NumericField;
use MacFJA\RediSearch\Index\Builder\TagField;
use MacFJA\RediSearch\Index\Builder\TextField;
use Predis\Client;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.TooManyFields) -- Builder Class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) -- Builder Class
 */
class Builder implements \MacFJA\RediSearch\Builder
{
    public const STRUCTURE_HASH = 'HASH';

    public const MAXIMUM_ALLOWED_FIELDS = 1024;

    public const MAXIMUM_ALLOWED_TEXT_FIELDS = 128;

    public const SUPPORTED_LANGUAGES = [
        'arabic', 'danish', 'dutch', 'english', 'finnish', 'french', 'german', 'hungarian', 'italian', 'norwegian',
        'portuguese', 'romanian', 'russian', 'spanish', 'swedish', 'tamil', 'turkish', 'chinese',
    ];

    /** @var Client */
    private $redis;

    /** @var array<Field> */
    private $fields = [];

    /** @var null|string */
    private $name;

    /** @var null|string */
    private $structure;

    /** @var array<string> */
    private $prefix = [];

    /** @var null|string */
    private $filter;

    /** @var null|string */
    private $language;

    /** @var null|string */
    private $languageField;

    /** @var null|float */
    private $score;

    /** @var null|string */
    private $scoreField;

    /** @var null|string */
    private $payloadField;

    /** @var null|int */
    private $maxTextFields;

    /** @var bool */
    private $noOffsets = false;

    /** @var null|int */
    private $temporary;

    /** @var bool */
    private $noHighlight = false;

    /** @var bool */
    private $noFields = false;

    /** @var bool */
    private $noFrequencies = false;

    /** @var array<string> */
    private $stopWords = [];

    /** @var bool */
    private $skipInitialScan = false;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
        $this->reset();
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withHashStructure(): self
    {
        $this->structure = self::STRUCTURE_HASH;

        return $this;
    }

    /**
     * @param array<string> $prefix
     *
     * @throws Throwable
     *
     * @return $this
     */
    public function withPrefix(array $prefix): self
    {
        DataHelper::assert(count($prefix) > 0);
        DataHelper::assertArrayOf($prefix, 'string');
        $this->prefix = $prefix;

        return $this;
    }

    public function withFilter(string $expression): self
    {
        $this->filter = $expression;

        return $this;
    }

    public function withLanguage(string $language): self
    {
        DataHelper::assert(in_array($language, self::SUPPORTED_LANGUAGES, true), UnsupportedLanguageException::class);
        $this->language = $language;

        return $this;
    }

    public function withLanguageField(string $fieldName): self
    {
        $this->languageField = $fieldName;

        return $this;
    }

    public function withDefaultScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function withScoreField(string $fieldName): self
    {
        $this->scoreField = $fieldName;

        return $this;
    }

    public function withPayloadField(string $fieldName): self
    {
        $this->payloadField = $fieldName;

        return $this;
    }

    public function withMaxTextFields(int $max): self
    {
        $this->maxTextFields = $max;

        return $this;
    }

    public function withNoOffsets(bool $flag = true): self
    {
        $this->noOffsets = $flag;

        return $this;
    }

    public function withTemporary(int $ttl): self
    {
        $this->temporary = $ttl;

        return $this;
    }

    public function withNoHighlight(bool $flag = true): self
    {
        $this->noHighlight = $flag;

        return $this;
    }

    public function withNoFields(bool $flag = true): self
    {
        $this->noFields = $flag;

        return $this;
    }

    public function withNoFrequencies(bool $flag = true): self
    {
        $this->noFrequencies = $flag;

        return $this;
    }

    /**
     * @param array<string> $words
     *
     * @return $this
     */
    public function withStopWords(array $words): self
    {
        DataHelper::assert(count($words) > 0);
        DataHelper::assertArrayOf($words, 'string');
        $this->stopWords = $words;

        return $this;
    }

    public function withSkipInitialScan(bool $flag = true): self
    {
        $this->skipInitialScan = $flag;

        return $this;
    }

    public function reset(): \MacFJA\RediSearch\Builder
    {
        $this->fields = [];
        $this->name = null;
        $this->structure = null;
        $this->prefix = [];
        $this->filter = null;
        $this->language = null;
        $this->languageField = null;
        $this->score = null;
        $this->scoreField = null;
        $this->payloadField = null;
        $this->maxTextFields = null;
        $this->noOffsets = false;
        $this->temporary = null;
        $this->noHighlight = false;
        $this->noFields = false;
        $this->noFrequencies = false;
        $this->stopWords = [];
        $this->skipInitialScan = false;

        return $this;
    }

    /**
     * @deprecated Use \MacFJA\RediSearch\Index\Builder::execute()
     */
    public function create(): bool
    {
        return $this->execute();
    }

    public function execute(): bool
    {
        $this->validateData();
        $query = $this->createRequest();
        $result = $this->redis->executeRaw($query);

        if ('OK' === $result) {
            $this->reset();

            return true;
        }

        return false;
    }

    public function addField(Field $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function addTextField(string $name, bool $noStem = false, ?float $weight = null, ?string $phonetic = null, bool $sortable = false, bool $noIndex = false): self
    {
        return $this->addField(
            new TextField($name, $noStem, $weight, $phonetic, $sortable, $noIndex)
        );
    }

    public function addGeoField(string $name, bool $noIndex = false): self
    {
        return $this->addField(new GeoField($name, $noIndex));
    }

    public function addNumericField(string $name, bool $sortable = false, bool $noIndex = false): self
    {
        return $this->addField(new NumericField($name, $sortable, $noIndex));
    }

    public function addTagField(string $name, ?string $separator, bool $sortable = false, bool $noIndex = false): self
    {
        return $this->addField(new TagField($name, $separator, $sortable, $noIndex));
    }

    /**
     * @return array<float|int|string>
     */
    private function createRequest(): array
    {
        assert(is_string($this->name));

        $query = ['FT.CREATE', $this->name];
        if (is_string($this->structure)) {
            $query[] = 'ON';
            $query[] = $this->structure;
        }
        $query = RedisHelper::buildQueryList($query, [
            'PREFIX' => $this->prefix,
            'STOPWORDS' => $this->stopWords,
        ]);
        $query = RedisHelper::buildQueryBoolean($query, [
            'NOOFFSETS' => $this->noOffsets,
            'NOHL' => $this->noHighlight,
            'NOFIELDS' => $this->noFields,
            'NOFREQS' => $this->noFrequencies,
            'SKIPINITIALSCAN' => $this->skipInitialScan,
        ]);
        RedisHelper::buildQueryNotNull($query, [
            'FILTER' => $this->filter,
            'LANGUAGE' => $this->language,
            'LANGUAGE_FIELD' => $this->languageField,
            'SCORE' => $this->score,
            'SCORE_FIELD' => $this->scoreField,
            'PAYLOAD_FIELD' => $this->payloadField,
            'MAXTEXTFIELDS' => $this->maxTextFields,
            'TEMPORARY' => $this->temporary,
        ]);
        if (count($this->fields) > 0) {
            $query[] = 'SCHEMA';

            foreach ($this->fields as $field) {
                /** @var Field $field */
                $query = array_merge($query, $field->getQueryParts());
            }
        }

        return $query;
    }

    private function validateData(): bool
    {
        if (null === $this->name) {
            throw new MissingNameException();
        }
        if (count($this->fields) > self::MAXIMUM_ALLOWED_FIELDS) {
            throw new TooManyFields('Maximum allowed fields is 1024');
        }
        if (count(array_filter($this->fields, function (Field $field) {
            return Field::TYPE_TEXT === $field->getType();
        })) > self::MAXIMUM_ALLOWED_TEXT_FIELDS) {
            throw new TooManyFields('Maximum allowed Text field is 128');
        }

        return true;
    }
}
