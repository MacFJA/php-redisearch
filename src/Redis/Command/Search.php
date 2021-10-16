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

namespace MacFJA\RediSearch\Redis\Command;

use function assert;
use function count;
use InvalidArgumentException;
use function is_array;
use function is_int;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;
use MacFJA\RediSearch\Redis\Command\SearchCommand\FilterOption;
use MacFJA\RediSearch\Redis\Command\SearchCommand\GeoFilterOption;
use MacFJA\RediSearch\Redis\Command\SearchCommand\HighlightOption;
use MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption;
use MacFJA\RediSearch\Redis\Command\SearchCommand\SortByOption;
use MacFJA\RediSearch\Redis\Command\SearchCommand\SummarizeOption;
use MacFJA\RediSearch\Redis\Response\ArrayResponseTrait;
use MacFJA\RediSearch\Redis\Response\PaginatedResponse;
use MacFJA\RediSearch\Redis\Response\SearchResponseItem;

/**
 * @method PaginatedResponse<SearchResponseItem> parseResponse(mixed $data)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Search extends AbstractCommand implements PaginatedCommand
{
    use LanguageOptionTrait;
    use ArrayResponseTrait;

    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct(
            [
                'index' => new NamelessOption(null, '>=2.0.0'),
                'query' => new NamelessOption(null, '>=2.0.0'),
                'nocontent' => new FlagOption('NOCONTENT', false, '>= 2.0.0'),
                'verbatim' => new FlagOption('VERBATIM', false, '>= 2.0.0'),
                'nostopwords' => new FlagOption('NOSTOPWORDS', false, '>= 2.0.0'),
                'withscores' => new FlagOption('WITHSCORES', false, '>= 2.0.0'),
                'withpayloads' => new FlagOption('WITHPAYLOADS', false, '>= 2.0.0'),
                'withsortkeys' => new FlagOption('WITHSORTKEYS', false, '>= 2.0.0'),
                'filter' => [],
                'geofilter' => new GeoFilterOption(),
                'inkeys' => new NumberedOption('INKEYS', null, '>=2.0.0'),
                'infields' => new NumberedOption('INFIELDS', null, '>=2.0.0'),
                'return' => new NumberedOption('RETURN', null, '>=2.0.0'),
                'summarize' => new SummarizeOption(),
                'highlight' => new HighlightOption(),
                'slop' => CV::isNumeric(new NamedOption('SLOP', null, '>=2.0.0')),
                'inorder' => new FlagOption('INORDER', false, '>=2.0.0'),
                'language' => $this->getLanguageOptions(),
                'expander' => new NamedOption('EXPANDER', null, '>=2.0.0'),
                'scorer' => new NamedOption('SCORER', null, '>=2.0.0'),
                'explainscore' => new FlagOption('EXPLAINSCORE', false, '>=2.0.0'),
                'payload' => new NamedOption('PAYLOAD', null, '>=2.0.0'),
                'sortby' => new SortByOption(),
                'limit' => new LimitOption(),
            ],
            $rediSearchVersion
        );
    }

    public function setIndex(string $index): self
    {
        $this->options['index']->setValue($index);

        return $this;
    }

    public function setQuery(string $query): self
    {
        $this->options['query']->setValue($query);

        return $this;
    }

    public function setNoContent(bool $active = true): self
    {
        $this->options['nocontent']->setActive($active);

        return $this;
    }

    public function setVerbatim(bool $active = true): self
    {
        $this->options['verbatim']->setActive($active);

        return $this;
    }

    public function setNoStopWords(bool $active = true): self
    {
        $this->options['nostopwords']->setActive($active);

        return $this;
    }

    public function setWithScores(bool $active = true): self
    {
        $this->options['withscores']->setActive($active);

        return $this;
    }

    public function setWithPayloads(bool $active = true): self
    {
        $this->options['withpayloads']->setActive($active);

        return $this;
    }

    public function setWithSortKeys(bool $active = true): self
    {
        $this->options['withsortkeys']->setActive($active);

        return $this;
    }

    /**
     * @param null|array<string> $fields
     *
     * @return $this
     */
    public function setSummarize(?array $fields = null, ?int $fragmentCount = null, ?int $fragmentSize = null, ?string $separator = null): self
    {
        $this->options['summarize']
            ->setDataOfOption('fields', $fields)
            ->setDataOfOption('frags', $fragmentCount)
            ->setDataOfOption('len', $fragmentSize)
            ->setDataOfOption('separator', $separator)
        ;

        return $this;
    }

    /**
     * @param null|array<string> $fields
     *
     * @return $this
     */
    public function setHighlight(?array $fields, ?string $openTag, ?string $closeTag): self
    {
        $this->options['highlight']
            ->setDataOfOption('fields', $fields)
            ->setTags($openTag, $closeTag)
        ;

        return $this;
    }

    public function setSlop(int $slop): self
    {
        $this->options['slop']->setValue($slop);

        return $this;
    }

    public function setInOrder(bool $active = true): self
    {
        $this->options['inorder']->setActive($active);

        return $this;
    }

    public function setLanguage(string $language): self
    {
        array_walk($this->options['language'], static function (CV $option) use ($language): void {
            $option->setValue($language);
        });

        return $this;
    }

    public function setExpander(string $function): self
    {
        $this->options['expander']->setValue($function);

        return $this;
    }

    public function setScorer(string $function): self
    {
        $this->options['scorer']->setValue($function);

        return $this;
    }

    public function setExplainScore(bool $active = true): self
    {
        $this->options['explainscore']->setActive($active);

        return $this;
    }

    public function setPayload(string $payload): self
    {
        $this->options['payload']->setValue($payload);

        return $this;
    }

    public function setSortBy(string $field, ?string $direction = null): self
    {
        $this->options['sortby']->setField($field)->setDirection($direction);

        return $this;
    }

    /**
     * @return $this
     */
    public function setLimit(int $offset, int $size): PaginatedCommand
    {
        $this->options['limit']->setOffset($offset)->setSize($size);

        return $this;
    }

    public function addFilter(FilterOption $filterOption): self
    {
        $this->options['filter'][] = $filterOption;

        return $this;
    }

    public function setGeoFilter(string $fieldName, float $longitude, float $latitude, float $radius, string $unit): self
    {
        $this->options['geofilter']->setDataOfOption('field', $fieldName)
            ->setDataOfOption('lon', $longitude)
            ->setDataOfOption('lat', $latitude)
            ->setDataOfOption('radius', $radius)
            ->setDataOfOption('unit', $unit)
        ;

        return $this;
    }

    public function setInKeys(string ...$key): self
    {
        $this->options['inkeys']->setArguments($key);

        return $this;
    }

    public function setInFields(string ...$field): self
    {
        $this->options['infields']->setArguments($field);

        return $this;
    }

    public function setReturn(string ...$field): self
    {
        $this->options['return']->setArguments($field);

        return $this;
    }

    public function getId(): string
    {
        return 'FT.SEARCH';
    }

    public function getOffset(): ?int
    {
        /** @var LimitOption $limit */
        $limit = $this->options['limit'];

        return $limit->getOffset();
    }

    public function getSize(): ?int
    {
        /** @var LimitOption $limit */
        $limit = $this->options['limit'];

        return $limit->getSize();
    }

    /**
     * @param mixed $data
     *
     * @return mixed|PaginatedResponse
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function transformParsedResponse($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $totalCount = array_shift($data);
        assert(is_int($totalCount));

        $useScores = $this->options['withscores']->isActive();
        $usePayloads = $this->options['withpayloads']->isActive();
        $useSortKeys = $this->options['withsortkeys']->isActive();
        $noContent = $this->options['nocontent']->isActive();

        $chunkSize = 2 // Hash + fields
            + (true === $useScores ? 1 : 0)
            + (true === $usePayloads ? 1 : 0)
            + (true === $useSortKeys ? 1 : 0);

        $documents = array_chunk($data, $chunkSize);

        $items = array_map(static function ($document) use ($useSortKeys, $usePayloads, $useScores, $noContent) {
            $hash = array_shift($document) ?? '';
            $score = true === $useScores ? (float) array_shift($document) : null;
            $payload = true === $usePayloads ? array_shift($document) : null;
            $sortKey = true === $useSortKeys ? array_shift($document) : null;

            $fields = [];
            if (false === $noContent) {
                if (!(1 === count($document))) {
                    throw new InvalidArgumentException();
                }
                $rawData = reset($document);
                assert(is_array($rawData));
                $fields = self::getPairs($rawData);
            }

            return new SearchResponseItem($hash, $fields, $score, $payload, $sortKey);
        }, $documents);

        return new PaginatedResponse($this, $totalCount, $items);
    }

    protected function getRequiredOptions(): array
    {
        return ['index', 'query'];
    }
}
