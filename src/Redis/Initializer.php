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

namespace MacFJA\RediSearch\Redis;

use function count;
use MacFJA\RediSearch\Redis\Command\Aggregate;
use MacFJA\RediSearch\Redis\Command\AliasAdd;
use MacFJA\RediSearch\Redis\Command\AliasDel;
use MacFJA\RediSearch\Redis\Command\AliasUpdate;
use MacFJA\RediSearch\Redis\Command\Alter;
use MacFJA\RediSearch\Redis\Command\Config;
use MacFJA\RediSearch\Redis\Command\ConfigSet;
use MacFJA\RediSearch\Redis\Command\Create;
use MacFJA\RediSearch\Redis\Command\CursorDel;
use MacFJA\RediSearch\Redis\Command\CursorRead;
use MacFJA\RediSearch\Redis\Command\DictAdd;
use MacFJA\RediSearch\Redis\Command\DictDel;
use MacFJA\RediSearch\Redis\Command\DictDump;
use MacFJA\RediSearch\Redis\Command\DropIndex;
use MacFJA\RediSearch\Redis\Command\Explain;
use MacFJA\RediSearch\Redis\Command\ExplainCli;
use MacFJA\RediSearch\Redis\Command\IndexList;
use MacFJA\RediSearch\Redis\Command\Info;
use MacFJA\RediSearch\Redis\Command\Search;
use MacFJA\RediSearch\Redis\Command\SpellCheck;
use MacFJA\RediSearch\Redis\Command\SugAdd;
use MacFJA\RediSearch\Redis\Command\SugDel;
use MacFJA\RediSearch\Redis\Command\SugGet;
use MacFJA\RediSearch\Redis\Command\SugLen;
use MacFJA\RediSearch\Redis\Command\SynDump;
use MacFJA\RediSearch\Redis\Command\SynUpdate;
use MacFJA\RediSearch\Redis\Command\TagVals;
use Predis\Profile\RedisProfile;
use Rediska_Command_Abstract;
use Rediska_Commands;
use Rediska_Connection_Exec;

if (class_exists(Rediska_Command_Abstract::class) && class_exists(Rediska_Connection_Exec::class)) {
    /**
     * @codeCoverageIgnore
     */
    class RediskaRediSearch extends Rediska_Command_Abstract
    {
        public function create(): Rediska_Connection_Exec
        {
            $connections = $this->_rediska->getConnections();

            if (count($connections) > 1) {
                trigger_error('Warning, Multiple redis connections exists, only the first connection will be used', E_USER_NOTICE);
            }
            $connection = reset($connections);

            $commands = $this->_arguments;
            if (!('__redisearch' === $this->_name)) {
                array_unshift($commands, $this->_name);
            }

            return new Rediska_Connection_Exec($connection, $commands);
        }
    }
}

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Initializer
{
    /**
     * @codeCoverageIgnore
     */
    public static function registerCommandsPredis(RedisProfile $profile): void
    {
        $profile->defineCommand('ftaggregate', Aggregate::class);
        $profile->defineCommand('ftaliasadd', AliasAdd::class);
        $profile->defineCommand('ftaliasdel', AliasDel::class);
        $profile->defineCommand('ftaliasupdate', AliasUpdate::class);
        $profile->defineCommand('ftalter', Alter::class);
        $profile->defineCommand('ftconfig', Config::class);
        $profile->defineCommand('ftconfigset', ConfigSet::class);
        $profile->defineCommand('ftcreate', Create::class);
        $profile->defineCommand('ftcursordel', CursorDel::class);
        $profile->defineCommand('ftcursorread', CursorRead::class);
        $profile->defineCommand('ftdictadd', DictAdd::class);
        $profile->defineCommand('ftdictdel', DictDel::class);
        $profile->defineCommand('ftdictdump', DictDump::class);
        $profile->defineCommand('ftdropindex', DropIndex::class);
        $profile->defineCommand('ftexplain', Explain::class);
        $profile->defineCommand('ftexplaincli', ExplainCli::class);
        $profile->defineCommand('ftlist', IndexList::class);
        $profile->defineCommand('ftinfo', Info::class);
        $profile->defineCommand('ftsearch', Search::class);
        $profile->defineCommand('ftspellcheck', SpellCheck::class);
        $profile->defineCommand('ftsugadd', SugAdd::class);
        $profile->defineCommand('ftsugdel', SugDel::class);
        $profile->defineCommand('ftsugget', SugGet::class);
        $profile->defineCommand('ftsuglen', SugLen::class);
        $profile->defineCommand('ftsyndump', SynDump::class);
        $profile->defineCommand('ftsynupdate', SynUpdate::class);
        $profile->defineCommand('fttagvals', TagVals::class);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function registerCommandsRediska(): void
    {
        Rediska_Commands::add('ftaggregate', RediskaRediSearch::class);
        Rediska_Commands::add('ftaliasadd', RediskaRediSearch::class);
        Rediska_Commands::add('ftaliasdel', RediskaRediSearch::class);
        Rediska_Commands::add('ftaliasupdate', RediskaRediSearch::class);
        Rediska_Commands::add('ftalter', RediskaRediSearch::class);
        Rediska_Commands::add('ftconfig', RediskaRediSearch::class);
        Rediska_Commands::add('ftconfigset', RediskaRediSearch::class);
        Rediska_Commands::add('ftcreate', RediskaRediSearch::class);
        Rediska_Commands::add('ftcursordel', RediskaRediSearch::class);
        Rediska_Commands::add('ftcursorread', RediskaRediSearch::class);
        Rediska_Commands::add('ftdictadd', RediskaRediSearch::class);
        Rediska_Commands::add('ftdictdel', RediskaRediSearch::class);
        Rediska_Commands::add('ftdictdump', RediskaRediSearch::class);
        Rediska_Commands::add('ftdropindex', RediskaRediSearch::class);
        Rediska_Commands::add('ftexplain', RediskaRediSearch::class);
        Rediska_Commands::add('ftexplaincli', RediskaRediSearch::class);
        Rediska_Commands::add('ftlist', RediskaRediSearch::class);
        Rediska_Commands::add('ftinfo', RediskaRediSearch::class);
        Rediska_Commands::add('ftsearch', RediskaRediSearch::class);
        Rediska_Commands::add('ftspellcheck', RediskaRediSearch::class);
        Rediska_Commands::add('ftsugadd', RediskaRediSearch::class);
        Rediska_Commands::add('ftsugdel', RediskaRediSearch::class);
        Rediska_Commands::add('ftsugget', RediskaRediSearch::class);
        Rediska_Commands::add('ftsuglen', RediskaRediSearch::class);
        Rediska_Commands::add('ftsyndump', RediskaRediSearch::class);
        Rediska_Commands::add('ftsynupdate', RediskaRediSearch::class);
        Rediska_Commands::add('fttagvals', RediskaRediSearch::class);
        Rediska_Commands::add('__redisearch', RediskaRediSearch::class);
    }

    public static function getRediSearchVersion(Client $client): ?string
    {
        $modules = $client->executeRaw('module', 'list') ?? [];

        foreach ($modules as $module) {
            $data = array_column(
                array_chunk(
                    $module,
                    2
                ),
                1,
                0
            );

            if (!(($data['name'] ?? '') === 'search') || empty($data['ver'])) {
                continue;
            }

            $major = floor($data['ver'] / 10000);
            $minor = floor(($data['ver'] - $major * 10000) / 100);
            $patch = $data['ver'] - $major * 10000 - $minor * 100;

            return sprintf(
                '%d.%d.%d',
                $major,
                $minor,
                $patch
            );
        }

        return null;
    }
}
