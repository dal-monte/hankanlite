<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$mysqli->query('DROP TABLE IF EXISTS customers');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    index(customer_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($createTableSql);
$mysqli->close();
