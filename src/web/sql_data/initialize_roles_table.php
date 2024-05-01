<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$mysqli->query('SET foreign_key_checks = 0');

$mysqli->query('DROP TABLE IF EXISTS roles');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS roles (
    role_id INT NOT NULL PRIMARY KEY,
    role VARCHAR(20) NOT NULL,
    role_name VARCHAR(30) NOT NULL,
    index(role_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;



$mysqli->query($createTableSql);
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
