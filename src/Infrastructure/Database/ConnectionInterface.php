<?php

namespace App\Infrastructure\Database;

interface ConnectionInterface
{
    public function beginTransaction(): bool;

    public function commit(): bool;

    public function errorCode(): ?string;

    public function errorInfo(): array;

    public function exec(string $statement): int|false;

    public function inTransaction(): bool;

    public function lastInsertId(string $name = null): string|false;

    public function prepare(string $statement, array $driverOptions = []): \PDOStatement|false;

    public function query(string $statement): \PDOStatement|false;

    public function quote(string $string, int $parameterType = \PDO::PARAM_STR): string|false;

    public function rollBack(): bool;
}

