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

use function array_chunk;
use function array_map;
use function array_merge;
use function array_shift;
use function assert;
use function count;
use function in_array;
use InvalidArgumentException;
use function is_array;
use function is_int;
use function is_string;
use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\PaginatedResult;
use MacFJA\RediSearch\Helper\PipelineItem;
use MacFJA\RediSearch\Helper\RedisHelper;
use MacFJA\RediSearch\Index\Builder as IndexBuilder;
use MacFJA\RediSearch\Search\Exception\UnsupportedLanguageException;
use MacFJA\RediSearch\Search\Filter;
use MacFJA\RediSearch\Search\GeoFilter;
use MacFJA\RediSearch\Search\Highlight;
use MacFJA\RediSearch\Search\Result;
use MacFJA\RediSearch\Search\Summarize;
use Predis\Client;
use function reset;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.TooManyFields) -- Builder Class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) -- Builder Class
 */
class Search implements Builder, Pipeable
{
    public const SORT_ASC = 'ASC';

    public const SORT_DESC = 'DESC';

    private const DEFAULT_OFFSET = 0;

    private const DEFAULT_LIMIT = 10;

    /** @var Client */
    private $redis;

    /** @var null|string */
    private $index;

    /** @var null|string */
    private $query;

    /** @var bool */
    private $noContent = false;

    /** @var bool */
    private $verbatim = false;

    /** @var bool */
    private $noStopWords = false;

    /** @var bool */
    private $withScores = false;

    /** @var bool */
    private $withPayloads = false;

    /** @var bool */
    private $withSortKeys = false;

    /** @var array<Filter> */
    private $filters = [];

    /** @var null|GeoFilter */
    private $geoFilter;

    /** @var array<string> */
    private $inKeys = [];

    /** @var array<string> */
    private $inFields = [];

    /** @var array<string> */
    private $returns = [];

    /** @var null|Summarize */
    private $summarize;

    /** @var null|Highlight */
    private $highlight;

    /** @var null|int */
    private $slop = null;

    /** @var bool */
    private $inOrder = false;

    /** @var null|string */
    private $language;

    /** @var null|string */
    private $expander;

    /** @var null|string */
    private $scorer;

    /** @var bool */
    private $explainScore = false;

    /** @var null|string */
    private $extensionPayload;

    /** @var null|string */
    private $sortBy;

    /** @var null|string */
    private $sortDirection;

    /** @var null|int */
    private $resultOffset;

    /** @var null|int */
    private $resultLimit;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
        $this->reset();
    }

    public function reset(): Builder
    {
        $this->index = null;
        $this->query = null;
        $this->noContent = false;
        $this->verbatim = false;
        $this->noStopWords = false;
        $this->withScores = false;
        $this->withPayloads = false;
        $this->withSortKeys = false;
        $this->filters = [];
        $this->geoFilter = null;
        $this->inKeys = [];
        $this->inFields = [];
        $this->returns = [];
        $this->summarize = null;
        $this->highlight = null;
        $this->slop = null;
        $this->inOrder = false;
        $this->language = null;
        $this->expander = null;
        $this->scorer = null;
        $this->explainScore = false;
        $this->extensionPayload = null;
        $this->sortBy = null;
        $this->sortDirection = null;
        $this->resultOffset = null;
        $this->resultLimit = null;

        return $this;
    }

    public function withIndex(string $index): Search
    {
        $this->index = $index;

        return $this;
    }

    public function withQuery(string $query): Search
    {
        $this->query = $query;

        return $this;
    }

    public function withNoContent(bool $noContent = true): Search
    {
        $this->noContent = $noContent;

        return $this;
    }

    public function withVerbatim(bool $verbatim = true): Search
    {
        $this->verbatim = $verbatim;

        return $this;
    }

    public function withoutStopWords(bool $noStopWords = true): Search
    {
        $this->noStopWords = $noStopWords;

        return $this;
    }

    public function withScores(bool $withScores = true): Search
    {
        $this->withScores = $withScores;

        return $this;
    }

    public function withPayloads(bool $withPayloads = true): Search
    {
        $this->withPayloads = $withPayloads;

        return $this;
    }

    public function withSortKeys(bool $withSortKeys = true): Search
    {
        $this->withSortKeys = $withSortKeys;

        return $this;
    }

    public function addFilter(string $numericField, float $min, float $max): self
    {
        $this->filters[] = new Filter($numericField, $min, $max);

        return $this;
    }

    public function withGeoFilter(string $fieldName, float $lon, float $lat, int $radius, string $unit): Search
    {
        $this->geoFilter = new GeoFilter($fieldName, $lon, $lat, $radius, $unit);

        return $this;
    }

    /**
     * @param array<string> $inKeys
     *
     * @throws Throwable If $inKeys is not an array of strings
     *
     * @return $this
     */
    public function withInKeys(array $inKeys): Search
    {
        DataHelper::assertArrayOf($inKeys, 'string');
        $this->inKeys = $inKeys;

        return $this;
    }

    /**
     * @param array<string> $inFields
     *
     * @throws Throwable If $inFields is not an array of strings
     *
     * @return $this
     */
    public function withInFields(array $inFields): Search
    {
        DataHelper::assertArrayOf($inFields, 'string');
        $this->inFields = $inFields;

        return $this;
    }

    /**
     * @param array<string> $returns
     *
     * @throws Throwable If $returns is not an array of strings
     *
     * @return $this
     */
    public function withReturns(array $returns): Search
    {
        DataHelper::assertArrayOf($returns, 'string');
        $this->returns = $returns;

        return $this;
    }

    /**
     * @param array<string> $fields
     * @param array<string> $fragments
     *
     * @return $this
     */
    public function withSummarize(array $fields = [], array $fragments = [], ?int $length = null, ?string $separator = null): Search
    {
        $this->summarize = new Summarize($fields, $fragments, $length, $separator);

        return $this;
    }

    /**
     * @param array<string> $fields
     *
     * @return $this
     */
    public function withHighlight(array $fields = [], ?string $openTag = null, ?string $closeTag = null): Search
    {
        $this->highlight = new Highlight($fields, $openTag, $closeTag);

        return $this;
    }

    public function withSlop(int $slop = 0): Search
    {
        $this->slop = $slop;

        return $this;
    }


    public function withInOrder(bool $inOrder = true): Search
    {
        $this->inOrder = $inOrder;

        return $this;
    }

    /**
     * @throws UnsupportedLanguageException
     *
     * @return $this
     */
    public function withLanguage(string $language): Search
    {
        DataHelper::assert(in_array($language, IndexBuilder::SUPPORTED_LANGUAGES, true), UnsupportedLanguageException::class);
        $this->language = $language;

        return $this;
    }

    public function withExpander(string $expander): Search
    {
        $this->expander = $expander;

        return $this;
    }

    public function withScorer(string $scorer): Search
    {
        $this->scorer = $scorer;

        return $this;
    }

    public function withExplainScore(bool $explainScore = true): Search
    {
        $this->explainScore = $explainScore;

        return $this;
    }

    public function withExtensionPayload(string $extensionPayload): Search
    {
        $this->extensionPayload = $extensionPayload;

        return $this;
    }

    public function sortBy(string $sortBy): Search
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function withSortDirection(string $sortDirection): Search
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    public function withResultOffset(int $resultOffset): Search
    {
        $this->resultOffset = $resultOffset;

        return $this;
    }

    public function withResultLimit(int $resultLimit): Search
    {
        $this->resultLimit = $resultLimit;

        return $this;
    }

    /**
     * @return array<Result>
     *
     * @deprecated Use \MacFJA\RediSearch\Search::execute()
     */
    public function search(): array
    {
        return $this->execute()->getItems();
    }

    /**
     * @return PaginatedResult<Result>
     */
    public function execute(): PaginatedResult
    {
        $request = $this->asPipelineItem();
        $raw = $this->redis->executeCommand($request->getCommand());

        return $request->transform($raw);
    }

    /**
     * @return array<string>|string
     */
    public function explainQuery(bool $asArray = false)
    {
        return $this->redis->executeRaw([true === $asArray ? 'FT.EXPLAINCLI' : 'FT.EXPLAIN', $this->index, $this->query]);
    }

    public function asPipelineItem(): PipelineItem
    {
        try {
            return PipelineItem::createFromRaw(
                $this->buildQuery(),
                static function ($rawResult, array $context): PaginatedResult {
                    DataHelper::handleRawResult($rawResult);

                    $totalCount = array_shift($rawResult);
                    assert(is_int($totalCount));

                    $chunkSize = 2 // Hash + fields
                        + (($context['scores'] ?? false) ? 1 : 0)
                        + (($context['payloads'] ?? false) ? 1 : 0)
                        + (($context['sortKeys'] ?? false) ? 1 : 0);

                    $documents = array_chunk($rawResult, $chunkSize);

                    $items = array_map(function ($document) use ($context) {
                        $hash = array_shift($document) ?? '';
                        $score = ($context['scores'] ?? false) ? (float) array_shift($document) : null;
                        $payload = ($context['payloads'] ?? false) ? array_shift($document) : null;
                        $sortKey = ($context['sortKeys'] ?? false) ? array_shift($document) : null;

                        $fields = [];
                        if (false === $context['noContent']) {
                            if (!(1 === count($document))) {
                                throw new InvalidArgumentException();
                            }
                            $rawData = reset($document);
                            assert(is_array($rawData));
                            $fields = RedisHelper::getPairs($rawData);
                        }

                        return new Result($hash, $fields, $score, $payload, $sortKey);
                    }, $documents);

                    return new class($totalCount, $items, $context['offset'] ?? self::DEFAULT_OFFSET, $context['limit'] ?? self::DEFAULT_LIMIT) extends PaginatedResult {
                    };
                },
                [
                    'offset' => $this->resultOffset,
                    'limit' => $this->resultLimit,
                    'scores' => $this->withScores,
                    'payloads' => $this->withPayloads,
                    'sortKeys' => $this->withSortKeys,
                    'noContent' => $this->noContent,
                ]
            );
        } finally {
            $this->reset();
        }
    }

    /**
     * @return array<float|int|string>
     */
    private function buildQuery(): array
    {
        assert(is_string($this->index));
        assert(is_string($this->query));

        $query = ['FT.SEARCH', $this->index, $this->query];
        $query = RedisHelper::buildQueryBoolean($query, [
            'NOCONTENT' => $this->noContent,
            'VERBATIM' => $this->verbatim,
            'NOSTOPWORDS' => $this->noStopWords,
            'WITHSCORES' => $this->withScores,
            'WITHPAYLOADS' => $this->withPayloads,
            'WITHSORTKEYS' => $this->withSortKeys,
            'EXPLAINSCORE' => $this->explainScore,
            'INORDER' => $this->inOrder,
        ]);
        $query = RedisHelper::buildQueryList($query, [
            'INKEYS' => $this->inKeys,
            'INFIELDS' => $this->inFields,
            'RETURN' => $this->returns,
        ]);
        $query = RedisHelper::buildQueryNotNull($query, [
            'LANGUAGE' => $this->language,
            'EXPANDER' => $this->expander,
            'SCORER' => $this->scorer,
            'PAYLOAD' => $this->extensionPayload,
        ]);
        $query = RedisHelper::buildQueryPartial($query,
            array_merge($this->filters, [$this->geoFilter, $this->summarize, $this->highlight])
        );
        if (is_int($this->slop)) {
            $query[] = 'SLOP';
            $query[] = $this->slop;
        }
        if (is_string($this->sortBy)) {
            $query[] = 'SORTBY';
            $query[] = $this->sortBy;
            if (is_string($this->sortDirection)) {
                $query[] = $this->sortDirection;
            }
        }
        if (is_int($this->resultOffset) && is_int($this->resultLimit)) {
            $query[] = 'LIMIT';
            $query[] = $this->resultOffset;
            $query[] = $this->resultLimit;
        }

        return $query;
    }
}
