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

$mysqli->query('DROP TABLE IF EXISTS companies');

$createTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS companies (
    company_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    index(company_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;



$mysqli->query($createTableSql);
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
