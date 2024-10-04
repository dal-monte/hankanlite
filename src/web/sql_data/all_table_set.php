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

$mysqli->query('DROP TABLE IF EXISTS categories');
$mysqli->query('DROP TABLE IF EXISTS customers');
$mysqli->query('DROP TABLE IF EXISTS employees');
$mysqli->query('DROP TABLE IF EXISTS products');
$mysqli->query('DROP TABLE IF EXISTS purchase_contracts');
$mysqli->query('DROP TABLE IF EXISTS purchase_products');
$mysqli->query('DROP TABLE IF EXISTS roles');
$mysqli->query('DROP TABLE IF EXISTS sales_contracts');
$mysqli->query('DROP TABLE IF EXISTS sales_products');
$mysqli->query('DROP TABLE IF EXISTS stocks');
$mysqli->query('DROP TABLE IF EXISTS suppliers');
$mysqli->query('DROP TABLE IF EXISTS users');


$rolesCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS roles (
    role_id INT NOT NULL PRIMARY KEY,
    role VARCHAR(20) NOT NULL,
    role_name VARCHAR(30) NOT NULL,
    index(role_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($rolesCreateTableSql);


$usersCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    index(role_id),
    FOREIGN KEY(role_id)
    REFERENCES roles(role_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($usersCreateTableSql);


$rolesInsertTableSql = <<<EOT
INSERT INTO roles (
    role,
    role_id,
    role_name
) VALUES (
    'root',
    '0',
    'root'
), (
    'admin',
    '1',
    'システム管理者'
), (
    'salesWorker',
    '2',
    '販売事務'
), (
    'purchaseWorker',
    '3',
    '仕入事務'
)
EOT;

$mysqli->query($rolesInsertTableSql);


$password = password_hash('hight882255', PASSWORD_DEFAULT);

$usersInsertTableSql = <<<EOT
INSERT INTO users (
    user_id,
    user_name,
    password,
    role_id
) VALUES (
    10,
    'root',
    '$password',
    '0'
)
EOT;

$mysqli->query($usersInsertTableSql);
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
