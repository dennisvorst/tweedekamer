<?php
declare(strict_types=1);

namespace App\Config;

use PDO;

class Database
{
    public static function createConnection(): PDO
    {
        $host = 'localhost';
        $db   = 'your_database';
        $user = 'your_username';
        $pass = 'your_password';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
?>