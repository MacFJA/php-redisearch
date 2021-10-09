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

use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait;

/**
 * @method TextFieldOption setField(string $name)
 * @method TextFieldOption setNoIndex(bool $active)
 * @method TextFieldOption setSortable(bool $active)
 * @method TextFieldOption setNoStem(bool $active)
 * @method TextFieldOption setWeight(?float $weight)
 * @method TextFieldOption setPhonetic(?string $phonetic)
 * @method TextFieldOption setUnNormalizedSortable(bool $unNormalized)
 */
class TextFieldOption extends GroupedOption implements CreateCommandFieldOption
{
    use BaseCreateFieldOptionTrait;
    use WithPublicGroupedSetterTrait;

    public function __construct()
    {
        parent::__construct($this->getConstructorOptions('TEXT', [
            'no_stem' => new FlagOption('NOSTEM', false, '>=2.0.0'),
            'weight' => CustomValidatorOption::isNumeric(new NamedOption('WEIGHT', null, '>=2.0.0')),
            'phonetic' => CustomValidatorOption::allowedValues(new NamedOption('PHONETIC', null, '>=2.0.0'), ['dm:en', 'dm:fr', 'dm:pt', 'dm:es']),
        ]), ['field', 'type'], ['type'], '>=2.0.0');
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
        return ['field', 'no_stem', 'weight', 'phonetic', 'sortable', 'no_index', 'un_normalized_sortable'];
    }
}
