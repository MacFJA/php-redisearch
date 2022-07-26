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
use function is_array;

use MacFJA\RediSearch\Exception\UnexpectedServerResponseException;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\ApplyOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\GroupByOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\LimitOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\SortByOption;
use MacFJA\RediSearch\Redis\Command\AggregateCommand\WithCursor;
use MacFJA\RediSearch\Redis\Command\Option\CommandOption;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;
use MacFJA\RediSearch\Redis\Response\AggregateResponseItem;
use MacFJA\RediSearch\Redis\Response\ArrayResponseTrait;
use MacFJA\RediSearch\Redis\Response\CursorResponse;
use MacFJA\RediSearch\Redis\Response\PaginatedResponse;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Aggregate extends AbstractCommand implements PaginatedCommand
{
    use ArrayResponseTrait;

    /** @var CommandOption */
    private $lastAdded;

    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'index' => new NamelessOption(null, '>=2.0.0'),
            'query' => new NamelessOption(null, '>=2.0.0'),
            'verbatim' => new FlagOption('VERBATIM', false, '>= 2.0.0'),
            'load' => new NumberedOption('LOAD', null, '>=2.0.0'),
            'loadall' => CV::allowedValues(new NamedOption('LOAD', null, '>=2.0.13'), ['ALL']),
            'groupby' => [],
            'sortby' => new SortByOption(),
            'apply' => [],
            'limit' => new LimitOption(),
            'filter' => [],
            'cursor' => new WithCursor(),
            'dialect' => CV::isNumeric(new NamedOption('DIALECT', null, '>=2.4.3')),
        ], $rediSearchVersion);
    }

    public function setIndex(string $index): self
    {
        $this->options['index']->setValue($index);
        $this->lastAdded = $this->options['index'];

        return $this;
    }

    public function getIndex(): string
    {
        return $this->options['index']->getValue();
    }

    public function setQuery(string $query): self
    {
        $this->options['query']->setValue($query);
        $this->lastAdded = $this->options['query'];

        return $this;
    }

    public function setVerbatim(bool $active = true): self
    {
        $this->options['verbatim']->setActive($active);
        $this->lastAdded = $this->options['verbatim'];

        return $this;
    }

    public function addSortBy(string $field, ?string $direction = null): self
    {
        $this->options['sortby']->addField($field, $direction);

        return $this;
    }

    public function setSortByMax(int $max): self
    {
        $this->options['sortby']->setMax($max);

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

    public function addFilter(string $expression): self
    {
        $this->options['filter'][] = new NamedOption('FILTER', $expression);

        return $this;
    }

    public function addApply(string $expression, string $alias): self
    {
        $apply = new ApplyOption();
        $apply
            ->setDataOfOption('expression', $expression)
            ->setDataOfOption('alias', $alias)
        ;
        $apply->setParent($this->lastAdded);
        $this->lastAdded = $apply;
        $this->options['apply'][] = $apply;

        return $this;
    }

    public function addGroupBy(GroupByOption $group): self
    {
        $this->options['groupby'][] = $group;
        $this->lastAdded = $group;

        return $this;
    }

    public function setLoad(string ...$field): self
    {
        $this->options['load']->setArguments($field);
        $this->lastAdded = $this->options['load'];

        return $this;
    }

    public function setLoadAll(): self
    {
        $this->options['load']->setArguments(null);
        $this->options['loadall']->setValue('ALL');
        $this->lastAdded = $this->options['loadall'];

        return $this;
    }

    public function setDialect(int $version): self
    {
        $this->options['dialect']->setValue($version);

        return $this;
    }

    public function setWithCursor(?int $count = null, ?int $maxIdle = null): self
    {
        $this->options['cursor']
            ->setDataOfOption('enabled', true)
            ->setDataOfOption('count', $count)
            ->setDataOfOption('maxidle', $maxIdle)
        ;

        return $this;
    }

    public function setWithoutCursor(): self
    {
        $this->options['cursor']->setDataOfOption('enabled', false);

        return $this;
    }

    public function getId(): string
    {
        return 'FT.AGGREGATE';
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
     * @param array|mixed|string $data
     *
     * @return CursorResponse|PaginatedResponse<AggregateResponseItem>
     * @phpstan-return CursorResponse|PaginatedResponse
     */
    public function parseResponse($data)
    {
        if (!is_array($data)) {
            throw new UnexpectedServerResponseException($data);
        }

        if (true === $this->options['cursor']->getDataOfOption('enabled')) {
            return CursorRead::transformResponse(
                $data,
                $this->options['cursor']->getDataOfOption('count'),
                $this->getIndex(),
                $this->getRediSearchVersion()
            );
        }

        $totalCount = array_shift($data);
        assert(is_numeric($totalCount));
        $totalCount = (int) $totalCount;

        $items = array_map(static function (array $document) {
            return new AggregateResponseItem(self::getPairs($document));
        }, $data);

        return new PaginatedResponse($this, $totalCount, $items);
    }

    protected function sortArguments(array $arguments): array
    {
        /** @var array<ApplyOption> $applies */
        $applies = array_filter($arguments, static function (CommandOption $option) {
            return $option instanceof ApplyOption;
        });
        $withoutApplies = array_values(array_filter($arguments, static function (CommandOption $option) {
            return !($option instanceof ApplyOption);
        }));
        for ($index = count($withoutApplies); $index > 0; --$index) {
            $item = $withoutApplies[$index - 1];
            foreach ($applies as $apply) {
                if ($apply->getParent() === $item || (null === $apply->getParent() && 1 === $index)) {
                    array_splice($withoutApplies, $index, 0, [$apply]);
                }
            }
        }

        return $withoutApplies;
    }

    protected function getRequiredOptions(): array
    {
        return ['index', 'query'];
    }
}
