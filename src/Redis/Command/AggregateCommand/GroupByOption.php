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

namespace MacFJA\RediSearch\Redis\Command\AggregateCommand;

use function count;
use MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption;
use MacFJA\RediSearch\Redis\Command\PrefixFieldName;

class GroupByOption extends AbstractCommandOption
{
    use PrefixFieldName;

    /** @var array<ReduceOption> */
    private $reducers = [];

    /** @var array<string> */
    private $fields;

    /**
     * @param array<string>       $fields
     * @param array<ReduceOption> $reducers
     */
    public function __construct(array $fields, array $reducers = [])
    {
        $this->reducers = $reducers;
        $this->fields = $fields;
        parent::__construct('>=2.0.0');
    }

    public function addReducer(ReduceOption $reducer): self
    {
        $this->reducers[] = $reducer;

        return $this;
    }

    public function isValid(): bool
    {
        return true;
    }

    /**
     * @codeCoverageIgnore Simple getter
     *
     * @return array<string,mixed>
     */
    public function getOptionData()
    {
        return [
            'fields' => $this->fields,
            'reducers' => $this->reducers,
        ];
    }

    protected function doRender(?string $version): array
    {
        $groupBy = array_merge(['GROUPBY', count($this->fields)], self::prefixFields($this->fields));
        $reducers = array_reduce($this->reducers, static function ($flatten, ReduceOption $reduce) use ($version) {
            return array_merge($flatten, $reduce->render($version));
        }, []);

        return array_merge($groupBy, $reducers);
    }
}
