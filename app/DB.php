<?php

class DB
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            // Priority order of credentials to try
            $attempts = [
                ["host" => "localhost", "port" => "3306", "user" => "root", "pass" => "root", "name" => "hotel"],
                ["host" => "localhost", "port" => "3306", "user" => "root", "pass" => "",     "name" => "hotel"],
                ["host" => "localhost", "port" => "3307", "user" => "root", "pass" => "1234", "name" => "hotel"],
                ["host" => "localhost", "port" => "3307", "user" => "root", "pass" => "root", "name" => "hotel"],
                ["host" => "127.0.0.1", "port" => "3306", "user" => "root", "pass" => "root", "name" => "hotel"],
                ["host" => "127.0.0.1", "port" => "3306", "user" => "root", "pass" => "",     "name" => "hotel"]
            ];

            // Allow environment variable overrides if provided
            if (getenv('DB_HOST')) {
                array_unshift($attempts, [
                    "host" => getenv('DB_HOST'),
                    "port" => getenv('DB_PORT') ?: "3306",
                    "user" => getenv('DB_USER') ?: "root",
                    "pass" => getenv('DB_PASS') !== false ? getenv('DB_PASS') : "root",
                    "name" => getenv('DB_NAME') ?: "hotel"
                ]);
            }

            $lastError = null;
            foreach ($attempts as $cfg) {
                try {
                    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]);
                    self::$instance = $pdo;
                    break;
                } catch (PDOException $e) {
                    $lastError = $e;
                    // If database does not exist, try creating it
                    if ($e->getCode() == 1049) {
                        try {
                            $rootPdo = new PDO("mysql:host={$cfg['host']};port={$cfg['port']}", $cfg['user'], $cfg['pass']);
                            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
                            self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass']);
                            break;
                        } catch (Exception $ex) {
                            $lastError = $ex;
                        }
                    }
                }
            }

            if (!self::$instance) {
                error_log("Database connection failed: " . ($lastError ? $lastError->getMessage() : "Unknown error"));
                throw new Exception("Unable to connect to MySQL database. Please verify MySQL service is running.");
            }
        }

        return self::$instance;
    }
}
