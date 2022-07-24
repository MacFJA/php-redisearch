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

namespace MacFJA\RediSearch\Redis\Command\Option;

use function is_scalar;

use Composer\Semver\Semver;
use UnexpectedValueException;

abstract class AbstractCommandOption implements CommandOption
{
    /** @var null|string */
    private $versionConstraint;

    public function __construct(?string $versionConstraint = null)
    {
        $this->versionConstraint = $versionConstraint;
    }

    public function isCompatible(?string $version): bool
    {
        if (null === $version) {
            return true;
        }

        try {
            return Semver::satisfies($version, $this->versionConstraint ?? '*');
        } catch (UnexpectedValueException $exception) {
            return false;
        }
    }

    public function getVersionConstraint(): string
    {
        return $this->versionConstraint ?? '*';
    }

    public function render(?string $version = null): array
    {
        if (!$this->isCompatible($version) || !$this->isValid()) {
            return [];
        }

        return array_filter($this->doRender($version), static function ($item) { return is_scalar($item); });
    }

    /**
     * @return array<null|float|int|string>
     */
    abstract protected function doRender(?string $version): array;
}
