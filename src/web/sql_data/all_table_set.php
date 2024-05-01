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


$categoriesCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE KEY,
    index(category_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($categoriesCreateTableSql);


$customersCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    index(customer_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($customersCreateTableSql);


$employeesCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS employees (
    id INT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($employeesCreateTableSql);


$productsCreateTableSql = <<<EOT
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

$mysqli->query($productsCreateTableSql);


$purchaseContractsCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS purchase_contracts (
    supplier_id INT NOT NULL,
    purchase_contract_id INT NOT NULL PRIMARY KEY,
    index(supplier_id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(supplier_id)
    REFERENCES suppliers(supplier_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($purchaseContractsCreateTableSql);


$purchaseProductsCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS purchase_products (
    purchase_contract_id INT NOT NULL,
    product_id INT NOT NULL,
    number INT NOT NULL,
    purchase_price INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    index(product_id),
    index(purchase_contract_id),
    FOREIGN KEY(purchase_contract_id)
    REFERENCES purchase_contracts(purchase_contract_id) ON DELETE cascade,
    FOREIGN KEY(product_id)
    REFERENCES products(product_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($purchaseProductsCreateTableSql);


$rolesCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS roles (
    role_id INT NOT NULL PRIMARY KEY,
    role VARCHAR(20) NOT NULL,
    role_name VARCHAR(30) NOT NULL,
    index(role_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($rolesCreateTableSql);


$salesContractsCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS sales_contracts (
    customer_id INT NOT NULL,
    sales_contract_id INT NOT NULL PRIMARY KEY,
    index(customer_id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(customer_id)
    REFERENCES customers(customer_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($salesContractsCreateTableSql);


$salesProductsCreateTableSql = <<<EOT
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

$mysqli->query($salesProductsCreateTableSql);


$stocksCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS stocks (
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    index(product_id),
    FOREIGN KEY(product_id)
    REFERENCES products(product_id) ON DELETE cascade
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($stocksCreateTableSql);


$suppliersCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    index(supplier_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$mysqli->query($suppliersCreateTableSql);


$usersCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
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

$mysqli->query($usersInsertTableSql);
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
