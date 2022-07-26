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

use function is_bool;
use function is_scalar;

class OptionListOption extends AbstractCommandOption
{
    /** @var array<CommandOption> */
    private $options = [];

    public function addOption(CommandOption $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function setValue(CommandOption ...$options): self
    {
        $this->options = $options;

        return $this;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getOptionData()
    {
        return $this->options;
    }

    protected function doRender(?string $version): array
    {
        $arguments = array_filter($this->options, static function (CommandOption $option) use ($version) {
            return $option->isCompatible($version) && $option->isValid();
        });
        $arguments = array_reduce($arguments, static function ($carry, CommandOption $option) use ($version) {
            return array_merge($carry, $option->render($version));
        }, []);

        return array_filter($arguments, static function ($item) { return !is_bool($item) && is_scalar($item); });
    }
}
