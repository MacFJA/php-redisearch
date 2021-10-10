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
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;

/**
 * @method int parseResponse(mixed $data)
 */
class SugAdd extends AbstractCommand
{
    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'dictionary' => new NamelessOption(null, '>=2.0.0'),
            'suggestion' => new NamelessOption(null, '>=2.0.0'),
            'score' => CV::isNumeric(new NamelessOption(null, '>=2.0.0')),
            'incr' => new FlagOption('INCR', false, '>=2.0.0'),
            'payload' => new NamedOption('PAYLOAD', null, '>=2.0.0'),
        ], $rediSearchVersion);
    }

    public function setDictionary(string $name): self
    {
        $this->options['dictionary']->setValue($name);

        return $this;
    }

    public function setSuggestion(string $value): self
    {
        $this->options['suggestion']->setValue($value);

        return $this;
    }

    public function setScore(float $score): self
    {
        /** @var CV<NamelessOption>|NamelessOption $option */
        $option = $this->options['score'];
        $option->setValue($score);

        return $this;
    }

    public function setIncrement(bool $active = true): self
    {
        $this->options['incr']->setActive($active);

        return $this;
    }

    public function setPayload(string $payload): self
    {
        $this->options['payload']->setValue($payload);

        return $this;
    }

    public function getId()
    {
        return 'FT.SUGADD';
    }

    protected function getRequiredOptions(): array
    {
        return ['dictionary', 'suggestion', 'score'];
    }
}
