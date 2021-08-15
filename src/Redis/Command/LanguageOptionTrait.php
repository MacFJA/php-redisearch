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
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;

trait LanguageOptionTrait
{
    /**
     * @return array<CV<NamedOption>>
     */
    protected function getLanguageOptions(): array
    {
        $base = ['arabic', 'danish', 'dutch', 'english', 'finnish', 'french', 'german', 'hungarian', 'italian', 'norwegian', 'portuguese', 'romanian', 'russian', 'spanish', 'swedish', 'tamil', 'turkish', 'chinese'];
        $v20006 = array_merge($base, ['basque', 'catalan', 'greek', 'indonesian', 'irish', 'lithuanian', 'nepali']);
        $c200010 = array_merge($v20006, ['armenian', 'serbian', 'yiddish']);

        return [
            CV::allowedValues(new NamedOption('LANGUAGE', null, '>=2.0.0 <2.0.6'), $base),
            CV::allowedValues(new NamedOption('LANGUAGE', null, '>=2.0.6 <2.0.10'), $v20006),
            CV::allowedValues(new NamedOption('LANGUAGE', null, '^2.0.10'), $c200010),
        ];
    }
}
