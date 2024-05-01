<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$mysqli->query('DROP TABLE IF EXISTS stocks');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS stocks (
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    index(product_id),
    FOREIGN KEY(product_id)
    REFERENCES products(product_id) ON DELETE cascade
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($createTableSql);
$mysqli->close();
