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

use function array_key_exists;
use BadMethodCallException;
use function in_array;
use function is_bool;
use function is_scalar;
use OutOfRangeException;

class GroupedOption extends AbstractCommandOption
{
    use DecoratedOptionAwareTrait;

    /** @var array<string,CommandOption> */
    private $options;

    /** @var array<string> */
    private $required;

    /** @var array<string> */
    private $locked;

    /**
     * @param array<string,CommandOption> $options
     * @param array<string>               $required
     * @param array<string>               $locked
     */
    public function __construct(array $options, array $required, array $locked = [], ?string $versionConstraint = null)
    {
        $this->required = $required;
        $this->options = $options;
        $this->locked = $locked;
        parent::__construct($versionConstraint);
    }

    /**
     * @param null|array<mixed>|mixed $value
     *
     * @return $this
     */
    public function setDataOfOption(string $name, $value): self
    {
        if (!array_key_exists($name, $this->options)) {
            throw new OutOfRangeException('There is no option for '.$name);
        }
        if (in_array($name, $this->locked, true)) {
            throw new BadMethodCallException('The option '.$name.' can\'t be modified');
        }

        if ($this->methodExists($this->options[$name], 'setValue')) {
            $this->options[$name]->setValue($value);
        }
        if ($this->methodExists($this->options[$name], 'setActive')) {
            $this->options[$name]->setActive($value);
        }
        if ($this->methodExists($this->options[$name], 'setArguments')) {
            $this->options[$name]->setArguments($value);
        }

        return $this;
    }

    public function getOption(string $name): ?CommandOption
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return null|array<mixed>|mixed
     */
    public function getDataOfOption(string $name)
    {
        $option = $this->getOption($name);

        return null === $option ? null : $option->getOptionData();
    }

    /**
     * @return array<string>
     */
    public function getOptionsName(): array
    {
        return array_keys($this->options);
    }

    public function isValid(): bool
    {
        foreach ($this->required as $requiredOption) {
            if (!array_key_exists($requiredOption, $this->options) || !$this->options[$requiredOption]->isValid()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<CommandOption>
     */
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
