<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$mysqli->query('DROP TABLE IF EXISTS employees');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS employees (
    id INT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($createTableSql);
$mysqli->close();
