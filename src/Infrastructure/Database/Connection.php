<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOStatement;
use PDOException;

class Connection extends PDO implements ConnectionInterface
{
    /**
     * Constructor to initialize the database connection.
     *
     * @param string $host       The hostname of the database server.
     * @param string $username   The username for the database connection.
     * @param string $password   The password for the database connection.
     * @param string|null $databaseName The name of the database to connect to.
     * @param string $charset    The character set for the database connection.
     * @param string $driver     The database driver (default is 'mysql').
     * @param array|null $options Additional options for the PDO connection (default is to set the default fetch mode to FETCH_ASSOC).
     * 
     * @throws PDOException If the connection fails.
     */
    public function __construct(
        string $host,
        string $databaseName,
        string $username,
        string $password,
        string $charset = 'utf8',
        string $driver = 'mysql',
        array $options = null
    ) {
        $dsn = "$driver:host=$host;dbname=$databaseName;charset=$charset";

        $options = $options === null ? [
            self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC
        ] : $options;

        parent::__construct(
            $dsn,
            $username,
            $password,
            $options
        );
    }

    /**
     * Creates a database connection from an array of settings.
     *
     * @param array $settings An associative array of database settings. Must include 'host', 'username', 'password', and 'dbname'.
     *
     * @return Connection An instance of the Connection class.
     * 
     * @throws \PDOException If the connection fails.
     */
    public static function createFromSettings(array $settings): Connection
    {
        return new Connection(
            $settings['host'],
            $settings['dbname'],
            $settings['username'],
            $settings['password']
        );
    }

    /**
     * Sets the parameter bindings for a prepared statement.
     *
     * @param \PDOStatement $statement The prepared statement.
     * @param array $binds An associative array of parameter bindings.
     *
     * @return \PDOStatement The prepared statement with bindings set.
     */
    public function setBinds(PDOStatement $statement, array $binds = []): PDOStatement
    {
        foreach ($binds as $key => $value) {
            $statement->bindValue($key, $value);
        }

        return $statement;
    }

    /**
     * Prepares and executes a database query with parameter bindings.
     *
     * @param string $query The SQL query to execute.
     * @param array $binds An associative array of parameter bindings.
     * @param bool $fetchAll Whether to fetch all rows or just one.
     *
     * @return array An associative array of query results.
     */
    public function fetchPrepare(string $query, array $binds = [], bool $fetchAll = true): array
    {
        $statement = $this->prepare($query);
        $this->setBinds($statement, $binds);

        if (!$statement->execute()) {
            return [];
        }

        $result = $fetchAll ? $statement->fetchAll() : $statement->fetch();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * Executes a direct database query without parameter bindings.
     *
     * @param string $query The SQL query to execute.
     * @param bool $fetchAll Whether to fetch all rows or just one.
     *
     * @return array An associative array of query results.
     */
    public function fetchQuery(string $query, bool $fetchAll = true): array
    {
        $statement = $this->query($query);

        if (!$statement) {
            return [];
        }

        $result = $fetchAll ? $statement->fetchAll() : $statement->fetch();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * Prepares and executes a database query with parameter bindings.
     *
     * @param string $query The SQL query to execute.
     * @param array $binds An associative array of parameter bindings.
     *
     * @return bool True if the query was executed successfully, otherwise false.
     */
    public function directPrepare(string $query, array $binds = []): bool
    {
        $statement = $this->prepare($query);
        $this->setBinds($statement, $binds);

        $result = $statement->execute();

        return $result;
    }

    /**
     * Executes a direct database query without parameter bindings.
     *
     * @param string $query The SQL query to execute.
     *
     * @return bool True if the query was executed successfully, otherwise false.
     */
    public function directQuery(string $query): bool
    {
        $statement = $this->query($query);

        if (!$statement) {
            return false;
        }

        return true;
    }

    /**
     * Starts a database transaction to execute the callback passed by arguments.
     * The callback must return a boolean value: True if the transaction must commit the changes, or false if the transaction must rollback the changes.
     *
     * @param callable $callback The callback function to be executed within the transaction.
     * 
     * @return bool The result of the callback decision (True for commit and false for rollback).
     * 
     * @throws \Exception Any Exception throwed by the callback
     */
    public function transact(callable $callback): bool
    {
        if ($this->inTransaction()) {
            return false;
        }

        if (!$this->beginTransaction()) {
            return false;
        }

        try {
            $success = $callback();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }

        if (!$success) {
            $this->rollBack();
        } else {
            $this->commit();
        }

        return $success;
    }
}
