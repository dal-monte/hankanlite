<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# データベースに接続する
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysqli->connect_error) {
    throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
}

$password = password_hash('hight882255', PASSWORD_DEFAULT);

$insertTableSql = <<<EOT
INSERT INTO users (
    user_id,
    name,
    password,
    role_id
) VALUES (
    10,
    'root',
    '$password',
    '0'
)
EOT;

$mysqli->query($insertTableSql);
$mysqli->close();
