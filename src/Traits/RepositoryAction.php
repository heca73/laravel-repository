<?php

namespace Heca73\LaravelRepository\Traits;

use Heca73\LaravelRepository\Exceptions\EmptyWhereClauseException;
use Heca73\LaravelRepository\Exceptions\QueryNotFoundException;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use stdClass;
use Throwable;

trait RepositoryAction
{
    /**
     * parameter separator token
     *
     * @var string $parameter_separator
     */
    protected string $parameter_separator = '|';

    /**
     * Separator token for in query
     *
     * @var string $in_query_separator
     */
    protected string $in_query_separator = ";";

    /**
     * Default value for query offset
     *
     * @var int $default_offset
     */
    protected int $default_offset = 0;

    /**
     * Default value for query limit
     *
     * @var int $default_limit
     */
    protected int $default_limit = 10;

    /**
     * Perform data finding based on given parameters
     *
     * @param array $selects
     * @param array $where
     * @param string|array $order_by
     * @param string|array $group_by
     * @param string|null $limit
     * @param int|null $offset
     * @param array $special_parameters
     * @param bool $distinct
     * @return Collection
     */
    public function find(array $selects = [], array $where = [], string|array $order_by = [], string|array $group_by = [], ?string $limit = null, ?int $offset = null, array $special_parameters = [], bool $distinct = true): Collection
    {
        $query = $this->createFindQuery($selects, $where, $order_by, $group_by, $limit, $offset, $special_parameters)->addDefaultJoins()->getBuilder();

        return $distinct ? $query->distinct()->get() : $query->get();
    }

    /**
     * Perform table joins
     * You should extend this method and add your
     * own join rules
     *
     * @return static
     */
    protected function addDefaultJoins(): static
    {
        // Todo: Add your own table joins here

        return $this;
    }

    /**
     * Create basic find query based on given data
     *
     * @param array $selects
     * @param array $wheres
     * @param array $orders
     * @param array $groups
     * @param int|null $limit
     * @param int|null $offset
     * @param array $special_parameters
     * @return static
     */
    protected function createFindQuery(array $selects = [], array $wheres = [], array $orders = [], array $groups = [], ?int $limit = null, ?int $offset = null, array $special_parameters = []): static
    {
        return $this->addSelects($selects)->addWheres($wheres)->addOrders($orders)->addGroups($groups)->addLimit($limit)->addOffset($offset)->addSpecialParameters($special_parameters);
    }

    /**
     * Perform special action for unique and special parameters
     * You should extend this method and add your own rules
     *
     * @param array $special_parameters
     * @return static
     */
    protected function addSpecialParameters(array $special_parameters = []): static
    {
        // Todo: Add your own special rules here

        return $this;
    }

    /**
     * Add offset to query
     *
     * @param int|null $offset
     * @return static
     */
    protected function addOffset(?int $offset = null): static
    {
        $this->getBuilder()->offset($offset ?? $this->default_offset);

        return $this;
    }

    /**
     * Limit query data
     *
     * @param int|null $limit
     * @return $this
     */
    protected function addLimit(null|int $limit = null): static
    {
        $this->getBuilder()->limit($limit ?? $this->default_limit);

        return $this;
    }

    /**
     * Add grouping clause to query
     *
     * @param array $groups
     * @return $this
     */
    protected function addGroups(array $groups = []): static
    {
        if (!is_array($groups)) $groups = [$groups];

        foreach ($groups as $column) {
            $this->getBuilder()->groupBy($this->resolvePrefixedColumn($column));
        }

        return $this;
    }

    /**
     * Resolve prefixed column with table name
     *
     * @param string $column
     * @return string
     */
    protected function resolvePrefixedColumn(string $column): string
    {
        return !str_contains($column, '.') ? $this->getTableName() . '.' . $column : $column;
    }

    /**
     * Add order clause to query
     *
     * @param array $orders
     * @return $this
     */
    protected function addOrders(array $orders = []): static
    {
        $orders = is_array($orders) ? $orders : [$orders];

        foreach ($orders as $column => $direction) {
            [$column, $direction] = $this->resolveOrderParamQuery($column, $direction);

            $this->getBuilder()->orderBy($this->resolvePrefixedColumn($column), $direction);
        }

        return $this;
    }

    /**
     * Resolve order by parameter before add id into query builder
     *
     * @param int|string $column
     * @param string $direction
     * @return array
     */
    protected function resolveOrderParamQuery(int|string $column, string $direction): array
    {
        if (is_integer($column)) $column = $direction;

        if (str_contains($direction, $this->parameter_separator)) {
            [$column, $direction] = explode($this->parameter_separator, $direction, 2);
        }

        $direction = strtolower($direction) == 'desc' ? 'desc' : 'asc';

        return [$column, $direction];
    }

    /**
     * Add where clause to the query
     *
     * @param array $wheres
     * @return $this
     */
    protected function addWheres(array $wheres = []): static
    {
        foreach ($wheres as $column => $parameter) {
            $column = $this->resolvePrefixedColumn($column);

            if (is_array($parameter)) {
                foreach ($parameter as $param) {
                    $this->buildWhereClause($column, $param);
                }
            } else {
                $this->buildWhereClause($column, $parameter);
            }
        }

        return $this;
    }

    /**
     * Build where clause before added into query builder
     *
     * @param string $column
     * @param string $parameter
     * @return void
     */
    protected function buildWhereClause(string $column, string $parameter): void
    {
        [$operator, $value] = $this->resolveParamQueryValue($parameter);

        if ($operator === 'null') {
            $value == 'true' ? $this->getBuilder()->whereNull($column) : $this->getBuilder()->WhereNotNull($column);
        } else if (strtoupper($operator) === 'in') {
            if (in_array('null', $value)) {
                unset($value[array_search('null', $value)]);
                $this->getBuilder()->where(function ($q) use ($column, $value) {
                    $q->whereNull($column)->orWhereIn($column, $value);
                });
            } else {
                $this->getBuilder()->whereIn($column, $value);
            }
        } else {
            $this->getBuilder()->where($column, $operator, $value);
        }
    }

    /**
     * @param string $parameter
     * @param string $operator
     * @return array
     */
    protected function resolveParamQueryValue(string $parameter, string $operator = '='): array
    {
        $value = $parameter;
        if (str_contains($parameter, $this->parameter_separator)) {
            [$type, $value] = explode($this->parameter_separator, $parameter, 2);
            $type = strtolower($type);
            $operator = $this->resolveWhereParamOperator($type, $operator);

            if (strtolower($type) == "in") {
                $operator = "in";
                $value = explode($this->in_query_separator, $value);
            }
        }

        return [$operator, $value];
    }

    /**
     * Resolve where param based on param type
     *
     * @param string $type
     * @param string $default
     * @return string
     */
    protected function resolveWhereParamOperator(string $type, string $default = '='): string
    {
        return ['neq' => '!=', 'lt' => '<', 'lte' => '<=', 'gt' => '>', 'gte' => '>=', 'like' => 'LIKE',][$type] ?? $default;
    }

    /**
     * Add table selects clause
     *
     * @param array|null $selects
     * @return $this
     */
    protected function addSelects(?array $selects = null): static
    {
        if (empty($selects)) $selects = $this->defaultSelects();

        foreach ($selects as $select) {
            if ($select instanceof Expression) {
                $this->getBuilder()->addSelect($select);
                continue;
            }
            $this->getBuilder()->addSelect($this->resolvePrefixedColumn($select));
        }

        return $this;
    }

    /**
     * Set default table selects
     * You should extend this method and add your own rules
     *
     * @return string[]
     */
    protected function defaultSelects(): array
    {
        // Todo: Change this with your own default table selects

        return [$this->getTableName() . '.*'];
    }


    /**
     * Update data
     *
     * @param array $data
     * @param bool $return_data
     * @return static|stdClass
     * @throws Throwable
     */
    public function create(array $data = [], bool $return_data = true): static|stdClass
    {
        return $this->builderShouldCreated()->getConnection()->transaction(function () use ($data, $return_data) {

            $this->getBuilder()->insert($data);
            $this->connection->commit();

            return (!$return_data) ? $this : $this->changeBuilderWhereClause()->getBuilder()->where($data)->first();
        });
    }

    /**
     * @param array $selects
     * @param array $where
     * @param string|array $order_by
     * @param string|array $group_by
     * @param string|null $limit
     * @param int|null $offset
     * @param array $special_parameters
     * @param bool $allow_null_result
     * @return stdClass|null
     */
    public function first(array $selects = [], array $where = [], string|array $order_by = [], string|array $group_by = [], ?string $limit = null, ?int $offset = null, array $special_parameters = [], bool $allow_null_result = false): ?stdClass
    {
        if (empty($selects)) $selects = $this->defaultSelects();

        $result = $this->createFindQuery($selects, $where, $order_by, $group_by, $limit, $offset, $special_parameters)->addDefaultJoins()->getBuilder()->first();

        if ($result || $allow_null_result) {
            return $result;
        }

        throw new QueryNotFoundException('Records from ' . $this->table_name . ' with given parameters no found');
    }

    /**
     * Change current where parameters in builder
     *
     * @param array $wheres
     * @return static
     */
    protected function changeBuilderWhereClause(array $wheres = []): static
    {
        $this->getBuilder()->wheres = [];
        $this->getBuilder()->bindings['where'] = [];

        foreach ($wheres as $where => $value) {
            $this->getBuilder()->where($this->resolvePrefixedColumn($where), $value);
        }

        return $this;
    }

    /**
     * Delete database record
     *
     * @param array $parameters
     * @param bool $return_data
     * @param bool $force_empty_where
     * @return static|Collection
     * @throws Throwable
     */
    public function delete(array $parameters = [], bool $return_data = true, bool $force_empty_where = false): static|Collection
    {
        return $this->builderShouldCreated()->getConnection()->transaction(function () use ($parameters, $return_data, $force_empty_where) {
            if (!empty($parameters)) $this->changeBuilderWhereClause($parameters);

            if (empty($this->getBuilder()->wheres) && !$force_empty_where) {
                throw new EmptyWhereClauseException();
            }

            $result = $return_data ? $this->getBuilder()->get() : $this;
            $this->getBuilder()->delete();
            return $result;
        });
    }

    /**
     * Update data
     *
     * @param array $updated_data
     * @param array $where
     * @param bool $return_data
     * @param bool $force_empty_where
     * @return static|Collection
     * @throws Throwable
     */
    public function update(array $updated_data = [], array $where = [], bool $return_data = true, bool $force_empty_where = false): static|Collection
    {
        return $this->builderShouldCreated()->getConnection()->transaction(function () use ($where, $updated_data, $return_data, $force_empty_where) {

            if (!empty($where)) $this->changeBuilderWhereClause($where);

            if (empty($this->getBuilder()->wheres) && !$force_empty_where) {
                throw new EmptyWhereClauseException();
            }

            $this->getBuilder()->update($updated_data);
            $this->connection->commit();

            if (!$return_data) return $this;

            $this->changeBuilderWhereClause($updated_data);

            return $this->getBuilder()->get();
        });
    }

    /**
     * Empty table record
     *
     * @throws Throwable
     */
    public function truncate(): void
    {
        $this->builderShouldCreated()->getConnection()->transaction(function () {
            $this->getBuilder()->truncate();
        });
    }

    /**
     * Get the first records by its id
     *
     * @param int $id
     * @param array $selects
     * @param bool $allow_null_result
     * @return stdClass|null
     */
    public function findById(int $id, array $selects = [], bool $allow_null_result = false): ?stdClass
    {
        return $this->first($selects, ['id' => $id], $allow_null_result);
    }
}