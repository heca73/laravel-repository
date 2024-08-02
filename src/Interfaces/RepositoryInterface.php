<?php

namespace Heca73\LaravelRepository\Interfaces;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use stdClass;

interface RepositoryInterface
{
    /**
     * Empty table record
     *
     * @return void
     */
    public function truncate(): void;

    /**
     * Search database records based on given parameters
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
    public function find(array $selects = [], array $where = [], string|array $order_by = [], string|array $group_by = [], ?string $limit = null, ?int $offset = null, array $special_parameters = [], bool $distinct = true): Collection;

    /**
     * Delete database record
     *
     * @param array $parameters
     * @param bool $return_data
     * @param bool $force_empty_where
     * @return static|Collection
     */
    public function delete(array $parameters = [], bool $return_data = true, bool $force_empty_where = false): static|Collection;

    /**
     * Update database record
     *
     * @param array $updated_data
     * @param array $where
     * @param bool $return_data
     * @param bool $force_empty_where
     * @return static|Collection
     */
    public function update(array $updated_data = [], array $where = [], bool $return_data = true, bool $force_empty_where = false): static|Collection;

    /**
     * Create new database records
     *
     * @param array $data
     * @param bool $return_data
     * @return static|stdClass
     */
    public function create(array $data = [], bool $return_data = true): static|stdClass;

    /**
     * Set new database connection name and create new
     * connection instance
     *
     * @param string $connection_name
     * @return static
     */
    public function setConnectionName(string $connection_name): static;

    /**
     * Get current database connection name
     *
     * @return string|null
     */
    public function getConnectionName(): ?string;

    /**
     * Set table name to used in this repository
     *
     * @param string $table_name
     * @return static
     */
    public function setTableName(string $table_name): static;

    /**
     * Get used table name for this repository
     *
     * @return string|null
     */
    public function getTableName(): ?string;

    /**
     * Get query database connection instance
     *
     * @return ConnectionInterface|null
     */
    public function getConnection(): ?ConnectionInterface;

    /**
     * Create query builder instance
     *
     * @param string|null $table_name
     * @param string|null $connection_name
     * @return static
     */
    public function createBuilder(?string $table_name = null, ?string $connection_name = null): static;

    /**
     * Get query builder instance
     *
     * @return Builder|null
     */
    public function getBuilder(): ?Builder;

    /**
     * Check whether the builder is created, if no, then
     * create it first
     *
     * @return static
     */
    public function builderShouldCreated(): static;
}