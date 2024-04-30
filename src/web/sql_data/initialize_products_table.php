<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$mysqli->query('SET foreign_key_checks = 0');

$mysqli->query('DROP TABLE IF EXISTS products');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE KEY,
    list_price INT NOT NULL,
    category_id INT NOT NULL,
    index(category_id),
    FOREIGN KEY(category_id)
    REFERENCES categories(category_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($createTableSql);
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
