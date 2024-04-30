<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$mysqli->query('SET foreign_key_checks = 0');

$mysqli->query('DROP TABLE IF EXISTS sales_products');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS sales_products (
    sales_contract_id INT NOT NULL,
    product_id INT NOT NULL,
    number INT NOT NULL,
    sales_price INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    index(sales_contract_id),
    index(product_id),
    FOREIGN KEY(sales_contract_id)
    REFERENCES sales_contracts(sales_contract_id) ON DELETE cascade,
    FOREIGN KEY(product_id)
    REFERENCES products(product_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($createTableSql);
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
