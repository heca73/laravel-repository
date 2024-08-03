<?php

namespace Heca73\LaravelRepository\Traits;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

trait RepositoryBuilder
{
    /**
     * Table name used by this repository
     *
     * @var string $table_name
     */
    protected string $table_name;

    /**
     * Connection / Database name used by this repository
     * For more information, read laravel documentation about
     * database connection
     *
     * @var string $connection_name
     */
    protected string $connection_name;

    /**
     * Laravel query builder instance that used to perform
     * database query in this repository
     *
     * @var Builder $builder
     */
    protected Builder $builder;

    /**
     * Laravel query connection instance
     *
     * @var ConnectionInterface $connection
     */
    protected ConnectionInterface $connection;

    /**
     * Check whether the builder is created, if no, then
     * create it first
     *
     * @return static
     */
    public function builderShouldCreated(): static
    {
        return !$this->getBuilder() ? $this->createBuilder() : $this;
    }

    /**
     * Get query builder instance
     *
     * @return Builder|null
     */
    public function getBuilder(): ?Builder
    {
        return $this->builder ?? null;
    }

    /**
     * Create query builder instance
     *
     * @param string|null $table_name
     * @param string|null $connection_name
     * @return static
     */
    public function createBuilder(?string $table_name = null, ?string $connection_name = null): static
    {
        if ($connection_name) $this->setConnectionName($connection_name);
        if (!$this->getConnection()) $this->setConnectionName($this->getConnectionName() ?? $this->getDefaultConnectionName());

        if ($table_name) $this->setTableName($table_name);
        if (!$this->getTableName()) $this->setTableName($this->getDefaultTableName());

        $this->builder = $this->getConnection()->table($this->getTableName());

        return $this;
    }

    /**
     * Get query database connection instance
     *
     * @return ConnectionInterface|null
     */
    public function getConnection(): ?ConnectionInterface
    {
        return $this->connection ?? null;
    }

    /**
     * Get the default connection name for the repository
     * you can just extend it in the child class and create
     * your own rules
     *
     * @return string
     */
    protected function getDefaultConnectionName(): string
    {
        $namespace = explode('\\', get_called_class());
        array_pop($namespace);
        $connection_name = array_pop($namespace);

        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $connection_name));
    }

    /**
     * Get used table name for this repository
     *
     * @return string|null
     */
    public function getTableName(): ?string
    {
        return $this->table_name ?? null;
    }

    /**
     * Set table name to used in this repository
     *
     * @param string $table_name
     * @return static
     */
    public function setTableName(string $table_name): static
    {
        $this->table_name = $table_name;

        return $this;
    }

    /**
     * Get the default table name used by the repository
     * You can just extend this method in child class and
     * define your own rules.
     *
     * @return string
     */
    protected function getDefaultTableName(): string
    {
        $namespace = explode('\\', get_called_class());
        $table_name = array_pop($namespace);

        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $table_name));
    }

    /**
     * Get current database connection name
     *
     * @return string|null
     */
    public function getConnectionName(): ?string
    {
        return $this->connection_name ?? null;
    }

    /**
     * Set new database connection name and create new
     * connection instance
     *
     * @param string $connection_name
     * @return static
     */
    public function setConnectionName(string $connection_name): static
    {
        $this->connection_name = $connection_name;

        $this->createConnection();

        return $this;
    }

    /**
     * Create new database connection instance
     *
     * @return static
     */
    protected function createConnection(): static
    {
        $this->connection = DB::connection($this->connection_name);

        return $this;
    }
}