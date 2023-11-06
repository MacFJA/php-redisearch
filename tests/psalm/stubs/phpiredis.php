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

const PHPIREDIS_READER_STATE_COMPLETE = 1;
const PHPIREDIS_READER_STATE_INCOMPLETE = 2;
const PHPIREDIS_READER_STATE_ERROR = 3;

const PHPIREDIS_REPLY_STRING = 1;
const PHPIREDIS_REPLY_ARRAY = 2;
const PHPIREDIS_REPLY_INTEGER = 3;
const PHPIREDIS_REPLY_NIL = 4;
const PHPIREDIS_REPLY_STATUS = 5;
const PHPIREDIS_REPLY_ERROR = 6;

function phpiredis_connect($host, $port = 6379): void {}
function phpiredis_pconnect($host, $port = 6379): void {}
function phpiredis_disconnect(): void {}

/**
 * @param resource $redis
 * @param string[] $command
 *
 * @return bool|string
 */
function phpiredis_command_bs($redis, array $command) {}

/**
 * @param resource $redis
 *
 * @return bool|string
 */
function phpiredis_command($redis, string $command) {}

/**
 * @param resource      $redis
 * @param array<string> $commands
 *
 * @return array<bool|string>
 */
function phpiredis_multi_command($redis, array $commands) {}

/**
 * @param resource             $redis
 * @param array<array<string>> $commands
 *
 * @return array<bool|string>
 */
function phpiredis_multi_command_bs($redis, array $commands) {}

/**
 * @param array<float|int|string> $args
 */
function phpiredis_format_command(array $args): string {}

/**
 * @return resource
 */
function phpiredis_reader_create() {}

/**
 * @param resource $reader
 */
function phpiredis_reader_reset($reader): void {}

/**
 * @param resource $reader
 */
function phpiredis_reader_feed($reader, string $response): void {}

/**
 * @param resource $reader
 *
 * @return bool|int
 */
function phpiredis_reader_get_state($reader) {}

/**
 * @param resource $reader
 *
 * @return bool|string
 */
function phpiredis_reader_get_error($reader) {}

/**
 * @param resource $reader
 *
 * @return array<array|bool|int|string>|bool|int|string
 */
function phpiredis_reader_get_reply($reader, int &$type) {}

/**
 * @param resource $redis
 */
function phpiredis_reader_destroy($redis): void {}

/**
 * @param resource $reader
 */
function phpiredis_reader_set_error_handler($reader, ?callable $handler): void {}

/**
 * @param resource $reader
 */
function phpiredis_reader_set_status_handler($reader, ?callable $handler): void {}
function phpiredis_utils_crc16(string $data): int {}
