<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$insertTableSql = <<<EOT
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

$mysqli->query($insertTableSql);
$mysqli->close();
