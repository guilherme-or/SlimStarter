<?php

declare(strict_types=1);

namespace App\Adapter\Database;

interface DatabaseConnectionInterface
{
    /**
     * Creates a new database connection
     * 
     * @param array $settings Associative settings array
     * to create a new database connection.
     * It must have 'host', 'username', 'password', and 'dbname' keys.
     * Other optional keys are 'driver' (default 'mysql'),
     * 'charset' (default 'utf8mb4'),
     * and 'pdo' (default exception mode and fetch associative array).
     * 
     * @throws DatabaseConnectionException
     */
    public function __construct(array $settings);

    /**
     * Fetch data from connected database
     * 
     * @param string $query SQL Query
     * @param array $binds Associative array of query binds, if used
     * @param bool $onlyOne Fetch only one associative array if true,
     * otherwise fetch a list of associative arrays
     * 
     * @throws DatabaseConnectionException
     * 
     * @return array List or associative array of query result
     */
    public function fetch(string $query, array $binds = [], bool $onlyOne = false): array;

    /**
     * Execute query in connected database
     * 
     * @param string $query SQL Query
     * @param array $binds Associative array of query binds, if used
     * 
     * @throws DatabaseConnectionException
     * 
     * @return void
     */
    public function execute(string $query, array $binds = []): void;

    /**
     * Manages a database transaction to execute the argument callback,
     * committing on success, rolling back on failure
     *
     * @param callable $callback The callback function to be executed within the transaction.
     * 
     * @throws \Exception|DatabaseConnectionException
     * Any Exception thrown by the callback or DatabaseConnectionException
     */
    public function transact(callable $callback): void;
}