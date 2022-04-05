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

use function is_array;
use MacFJA\RediSearch\Exception\UnexpectedServerResponseException;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\SpellCheckCommand\TermsOption;
use MacFJA\RediSearch\Redis\Response\SpellCheckResponseItem;
use Respect\Validation\Validator;

class SpellCheck extends AbstractCommand
{
    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'index' => new NamelessOption(null, '>=2.0.0'),
            'query' => new NamelessOption(null, '>=2.0.0'),
            'distance' => new CustomValidatorOption(
                new NamedOption('DISTANCE', null, '>=2.0.0'),
                Validator::intVal()->between(1, 4)
            ),
            'dialect' => CV::isNumeric(new NamedOption('DIALECT', null, '>=2.4.3')),
            'terms' => [],
        ], $rediSearchVersion);
    }

    public function setIndex(string $index): self
    {
        $this->options['index']->setValue($index);

        return $this;
    }

    public function setQuery(string $query): self
    {
        $this->options['query']->setValue($query);

        return $this;
    }

    public function setDistance(int $distance): self
    {
        $this->options['distance']->setValue($distance);

        return $this;
    }

    public function setDialect(int $version): self
    {
        $this->options['dialect']->setValue($version);

        return $this;
    }

    public function addTerms(string $dictionary, bool $isExcluding = false): self
    {
        $terms = new TermsOption();
        $terms->setIsExclusion($isExcluding);
        $terms->setDictionary($dictionary);
        $this->options['terms'][] = $terms;

        return $this;
    }

    public function getId(): string
    {
        return 'FT.SPELLCHECK';
    }

    /**
     * @param mixed $data
     *
     * @return SpellCheckResponseItem[]
     */
    public function parseResponse($data)
    {
        if (!is_array($data)) {
            throw new UnexpectedServerResponseException($data);
        }

        $items = array_map(static function (array $group) {
            $term = array_shift($group);
            if (!('TERM' === $term)) {
                return null;
            }
            $misspelled = array_shift($group);
            $suggestions = reset($group);
            $suggestions = array_column($suggestions, 0, 1);
            $castedSuggestions = [];
            foreach ($suggestions as $term => $score) {
                $castedSuggestions[(string) $term] = (float) $score;
            }

            return new SpellCheckResponseItem($misspelled, $castedSuggestions);
        }, $data);

        return array_filter($items);
    }

    protected function getRequiredOptions(): array
    {
        return ['index', 'query'];
    }
}
