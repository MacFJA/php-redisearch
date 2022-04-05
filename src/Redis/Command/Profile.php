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

use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\ProfileCommand\QueryOption;

class Profile extends AbstractCommand
{
    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'index' => new NamelessOption(null, '>=2.2.0'),
            'type' => CV::allowedValues(new NamelessOption(null, '>=2.2.0'), ['SEARCH', 'AGGREGATE']),
            'limited' => new FlagOption('LIMITED', false, '>=2.2.0'),
            'query' => new QueryOption('>=2.2.0'),
        ], $rediSearchVersion);
    }

    public function setIndex(string $index): self
    {
        $this->options['index']->setValue($index);

        return $this;
    }

    /**
     * @param Aggregate|Search $query
     */
    public function setQuery($query): self
    {
        /** @var QueryOption $queryOption */
        $queryOption = $this->options['query'];
        $queryOption->setCommand($query);
        $this->options['type']->setValue($queryOption->isSearch() ? 'SEARCH' : 'AGGREGATE');
        $this->options['index']->setValue($queryOption->getIndex());

        return $this;
    }

    public function setTypeAggregate(): self
    {
        $this->options['type']->setValue('AGGREGATE');

        return $this;
    }

    public function setTypeSearch(): self
    {
        $this->options['type']->setValue('SEARCH');

        return $this;
    }

    public function setLimited(bool $active = true): self
    {
        $this->options['limited']->setActive($active);

        return $this;
    }

    public function getId(): string
    {
        return 'FT.PROFILE';
    }

    protected function getRequiredOptions(): array
    {
        return ['index', 'type', 'query'];
    }
}
