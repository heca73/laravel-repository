<?php

namespace Heca73\LaravelRepository\Facades;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void truncate()
 * @method static Collection find(array $selects = [], array $where = [], string|array $order_by = [], string|array $group_by = [], ?string $limit = null, ?int $offset = null, array $special_parameters = [], bool $distinct = true)
 * @method static static|Collection delete(array $parameters = [], bool $return_data = true, bool $force_empty_where = false)
 * @method static static|Collection update(array $updated_data = [], array $where = [], bool $return_data = true, bool $force_empty_where = false)
 * @method static static|Collection create(array $data = [], bool $return_data = true)
 * @method static static setConnectionName(string $connection_name)
 * @method static null|string getConnectionName()
 * @method static static setTableName(string $table_name)
 * @method static null|string getTableName()
 * @method static null|ConnectionInterface getConnection()
 * @method static static createBuilder(?string $table_name = null, ?string $connection_name = null)
 * @method static null|Builder getBuilder():
 * @method static static builderShouldCreated()
 * @mixin \Heca73\LaravelRepository\Repository
 */
class Repository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'heca73-laravel-repository';
    }
}