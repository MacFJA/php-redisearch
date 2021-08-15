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

namespace MacFJA\RediSearch\Redis\Command\SearchCommand;

use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;

class GeoFilterOption extends GroupedOption
{
    public const UNITS = [self::UNIT_METERS, self::UNIT_KILOMETERS, self::UNIT_FEET, self::UNIT_MILES];
    public const UNIT_METERS = 'm';
    public const UNIT_KILOMETERS = 'km';
    public const UNIT_MILES = 'mi';
    public const UNIT_FEET = 'ft';

    public function __construct()
    {
        parent::__construct([
            'type' => new FlagOption('GEOFILTER', true, '>=2.0.0'),
            'field' => new NamelessOption(null, '>=2.0.0'),
            'lon' => CV::isNumeric(new NamelessOption(null, '>=2.0.0')),
            'lat' => CV::isNumeric(new NamelessOption(null, '>=2.0.0')),
            'radius' => CV::isNumeric(new NamelessOption(null, '>=2.0.0')),
            'unit' => CV::allowedValues(new NamelessOption(null, '>=2.0.0'), self::UNITS),
        ], ['type', 'field', 'lon', 'lat', 'radius', 'unit'], ['type'], '>=2.0.0');
    }
}
