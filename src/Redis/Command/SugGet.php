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

use function count;
use function is_array;
use MacFJA\RediSearch\Exception\UnexpectedServerResponseException;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Response\SuggestionResponseItem;

class SugGet extends AbstractCommand
{
    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'dictionary' => new NamelessOption(null, '>=2.0.0'),
            'prefix' => new NamelessOption(null, '>=2.0.0'),
            'fuzzy' => new FlagOption('FUZZY', false, '>=2.0.0'),
            'scores' => new FlagOption('WITHSCORES', false, '>=2.0.0'),
            'payloads' => new FlagOption('WITHPAYLOADS', false, '>=2.0.0'),
            'max' => CV::isNumeric(new NamedOption('MAX', null, '>=2.0.0')),
        ], $rediSearchVersion);
    }

    public function setDictionary(string $name): self
    {
        $this->options['dictionary']->setValue($name);

        return $this;
    }

    public function setPrefix(string $value): self
    {
        $this->options['prefix']->setValue($value);

        return $this;
    }

    public function setWithScores(bool $active = true): self
    {
        $this->options['scores']->setActive($active);

        return $this;
    }

    public function setFuzzy(bool $active = true): self
    {
        $this->options['fuzzy']->setActive($active);

        return $this;
    }

    public function setWithPayloads(bool $active = true): self
    {
        $this->options['payloads']->setActive($active);

        return $this;
    }

    public function setMax(int $max): self
    {
        /** @var CV<NamedOption> $option */
        $option = $this->options['max'];

        $option->setValue($max);

        return $this;
    }

    public function getId(): string
    {
        return 'FT.SUGGET';
    }

    /**
     * @param mixed $data
     *
     * @return SuggestionResponseItem[]
     */
    public function parseResponse($data)
    {
        if (!is_array($data)) {
            throw new UnexpectedServerResponseException($data);
        }

        $useScores = $this->options['scores']->isActive();
        $usePayloads = $this->options['payloads']->isActive();

        $chunkSize = 1 // value
            + (true === $useScores ? 1 : 0)
            + (true === $usePayloads ? 1 : 0);

        $documents = array_chunk($data, $chunkSize);

        return array_map(static function ($document) use ($data, $usePayloads, $useScores) {
            $payload = true === $usePayloads ? array_pop($document) : null;
            $score = true === $useScores ? (float) array_pop($document) : null;

            if (!(1 === count($document))) {
                throw new UnexpectedServerResponseException($data, 'Incomplete response');
            }
            $value = reset($document);

            return new SuggestionResponseItem($value, $score, $payload);
        }, $documents);
    }

    protected function getRequiredOptions(): array
    {
        return ['dictionary', 'prefix'];
    }
}
