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

namespace MacFJA\RedisSearch\Search\QueryBuilder;

use function in_array;
use MacFJA\RedisSearch\Helper\DataHelper;
use MacFJA\RedisSearch\Search\Exception\UnknownUnitException;
use MacFJA\RedisSearch\Search\GeoFilter;
use function sprintf;

class GeoFacet implements PartialQuery
{
    /** @var string */
    private $fieldName;

    /** @var float */
    private $lon;

    /** @var float */
    private $lat;

    /** @var int */
    private $radius;

    /** @var string */
    private $unit;

    /**
     * GeoFilter constructor.
     */
    public function __construct(string $fieldName, float $lon, float $lat, int $radius, string $unit)
    {
        DataHelper::assert(
            in_array($unit, [GeoFilter::UNIT_FEET, GeoFilter::UNIT_KILOMETERS, GeoFilter::UNIT_METERS, GeoFilter::UNIT_MILES], true),
            new UnknownUnitException($unit)
        );
        $this->fieldName = $fieldName;
        $this->lon = $lon;
        $this->lat = $lat;
        $this->radius = $radius;
        $this->unit = $unit;
    }

    public function render(): string
    {
        return sprintf('@%s:[%f %f %f %s]', $this->fieldName, $this->lon, $this->lat, $this->radius, $this->unit);
    }

    public function includeSpace(): bool
    {
        return false;
    }

    public function priority(): int
    {
        return self::PRIORITY_NORMAL;
    }
}
