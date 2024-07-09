<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOStatement;

class DatabaseConnection extends PDO implements DatabaseConnectionInterface
{
    private const PARAM_TYPES = [
        "string" => parent::PARAM_STR,
        "boolean" => parent::PARAM_BOOL,
        "integer" => parent::PARAM_INT,
        "double" => parent::PARAM_INT,
        "NULL" => parent::PARAM_NULL,
    ];

    public function __construct(array $settings)
    {
        if (
            !isset(
            $settings["host"],
            $settings["username"],
            $settings["password"],
            $settings["dbname"]
        )
        ) {
            throw new DatabaseConnectionException(
                "Missing database settings. It must have "
                . "'host', 'username', 'password', and 'dbname' keys"
            );
        }

        // $dsn = "$driver:host=$host;dbname=$databaseName;charset=$charset";
        $dsn = $settings["driver"] ?? "mysql";
        $dsn .= ":host=" . $settings["host"];
        $dsn .= ";dbname=" . $settings["dbname"];
        $dsn .= ";charset=" . ($settings["charset"] ?? "utf8mb4");

        parent::__construct(
            $dsn,
            $settings["username"],
            $settings["password"],
            $settings["pdo"] ?? [
                parent::ATTR_ERRMODE => parent::ERRMODE_EXCEPTION,
                parent::ATTR_DEFAULT_FETCH_MODE => parent::FETCH_ASSOC,
            ]
        );
    }

    private function prepareStatement(string $query, array $binds): PDOStatement
    {
        $stm = $this->prepare($query);

        if (!$stm) {
            throw new DatabaseConnectionException(
                "Error while creating statement. Query: $query. Binds: "
                . count($binds) > 0 ? join(", ", array_values($binds)) : "None"
            );
        }

        foreach ($binds as $key => $value) {
            $paramType = self::PARAM_TYPES[gettype($value)] ?? parent::PARAM_STR;

            if (!$stm->bindValue($key, $value, $paramType)) {
                throw new DatabaseConnectionException(
                    "Error binding statement values. Query: $query. Bind: $key. Value: $value"
                );
            }
        }

        return $stm;
    }

    public function fetch(string $query, array $binds = [], bool $all = true): array
    {
        $stm = $this->prepareStatement($query, $binds);

        if (!$stm->execute()) {
            throw new DatabaseConnectionException(
                "Error while executing statement. Query: $query. Binds: "
                . count($binds) <= 0 ? "None" : join(", ", array_values($binds))
            );
        }

        $result = $all ? $stm->fetchAll() : $stm->fetch();

        if (!$result) {
            throw new DatabaseConnectionException(
                "Error while fetching statement result. Query: $query. Binds: "
                . count($binds) <= 0 ? "None" : join(", ", array_values($binds))
            );
        }

        return $result;
    }

    public function execute(string $query, array $binds = []): void
    {
        $stm = $this->prepareStatement($query, $binds);

        if (!$stm->execute()) {
            throw new DatabaseConnectionException(
                "Error while executing statement result. Query: $query. Binds: "
                . count($binds) <= 0 ? "None" : join(", ", array_values($binds))
            );
        }
    }

    /**
     * Starts a database transaction to execute the callback passed by arguments.
     *
     * @param callable $callback The callback function to be executed within the transaction.
     * 
     * @throws \Exception|DatabaseConnectionException
     * Any Exception thrown by the callback or DatabaseConnectionException
     */
    public function transact(callable $callback): void
    {
        if ($this->inTransaction()) {
            throw new DatabaseConnectionException("Already in transaction");
        }

        if (!$this->beginTransaction()) {
            throw new DatabaseConnectionException("Error while starting new transaction");
        }

        try {
            $callback();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }

        $this->commit();
    }
}