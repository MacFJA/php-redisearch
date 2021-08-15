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

namespace MacFJA\RediSearch\Redis\Command\CreateCommand;

use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait;

/**
 * @method GeoFieldOption setField(string $name)
 * @method GeoFieldOption setNoIndex(bool $active)
 * @method GeoFieldOption setSortable(bool $active)
 */
class GeoFieldOption extends GroupedOption implements CreateCommandFieldOption
{
    use WithPublicGroupedSetterTrait;

    public function __construct()
    {
        parent::__construct([
            'field' => new NamelessOption(null, '>=2.0.0'),
            'type' => new FlagOption('GEO', true, '>=2.0.0'),
            'no_index' => new FlagOption('NOINDEX', false, '>=2.0.0'),
            'sortable' => new FlagOption('SORTABLE', false, '>=2.0.10'),
        ], ['field', 'type'], ['type'], '>=2.0.0');
    }

    public function getFieldName(): string
    {
        return $this->getDataOfOption('field');
    }

    /**
     * @return string[]
     */
    protected function publicSetter(): array
    {
        return ['field', 'no_index', 'sortable'];
    }
}
