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

namespace MacFJA\RediSearch\Redis\Client;

use function count;
use function strlen;

use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;

abstract class AbstractClient implements Client
{
    /** @var bool */
    public static $disableNotice = false;

    public function pipeline(Command ...$commands): array
    {
        $results = $this->doPipeline(...$commands);

        return array_map(static function ($result, $index) use ($commands) {
            return $commands[$index]->parseResponse($result);
        }, $results, array_keys($results));
    }

    /**
     * @return array<mixed>
     */
    abstract protected function doPipeline(Command ...$commands): array;

    protected static function fcqnExists(string $fcqn): bool
    {
        return class_exists($fcqn) || interface_exists($fcqn);
    }

    /**
     * @param array<string,array<string>> $classesMethods
     * @param array<string>               $functions
     */
    protected function getMissingMessage(string $name, bool $isExtension, array $classesMethods, array $functions = []): string
    {
        $classes = array_keys($classesMethods);
        $methods = array_map(static function (string $class, array $methods) {
            return array_map(static function ($method) use ($class) {
                return $class.'::'.$method;
            }, $methods);
        }, $classes, $classesMethods);
        $methods = array_reduce($methods, static function ($flat, $methods) {
            return array_merge($flat, $methods);
        }, []);

        $message = 'The dependency '.$name.' is missing.'.PHP_EOL.'Install the dependency';
        if (true === $isExtension) {
            $message = 'The extension '.$name.' is missing.'.PHP_EOL.'Install the extension';
        }
        $message .= ' or use a polyfill';

        $classesMessage = null;
        if (1 === count($classes)) {
            $classesMessage = 'the class "'.reset($classes).'"';
        } elseif (count($classes) > 1) {
            $classesMessage = 'the classes "'.implode('", "', $classes).'"';
        }
        $methodsMessage = null;
        if (1 === count($methods)) {
            $methodsMessage = 'the method "'.reset($methods).'"';
        } elseif (count($methods) > 1) {
            $methodsMessage = 'the methods "'.implode('", "', $methods).'"';
        }
        $functionsMessage = null;
        if (1 === count($functions)) {
            $functionsMessage = 'the function "'.reset($functions).'"';
        } elseif (count($functions) > 1) {
            $functionsMessage = 'the functions "'.implode('", "', $functions).'"';
        }

        $additional = implode(' and ', array_filter([$classesMessage, $methodsMessage, $functionsMessage]));
        if (strlen($additional) > 0) {
            $additional = ' that provide '.$additional;
        }
        $message .= $additional.'.';

        return $message;
    }
}
