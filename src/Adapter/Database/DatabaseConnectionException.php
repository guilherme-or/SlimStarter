<?php

declare(strict_types=1);

namespace App\Adapter\Database;

class DatabaseConnectionException extends \PDOException
{
    public $message = "Database communication error";
}