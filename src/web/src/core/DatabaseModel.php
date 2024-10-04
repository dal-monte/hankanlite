<?php

class DatabaseModel
{
    protected $mysqli;

    protected $sqlCommand;

    public function __construct($mysqli, $sqlCommand)
    {
        $this->mysqli = $mysqli;
        $this->sqlCommand = $sqlCommand;
    }

    public function fetchAll($sql)
    {
        $stmt = $this->mysqli->prepare($sql);

        $stmt->execute();

        /* 値を取得します */
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function execute($sql, $params = [])
    {

        $stmt = $this->mysqli->prepare($sql);

        $stmt->bind_param(...$params);

        $stmt->execute();
        $stmt->close();
    }

        public function querySet($sql)
    {
        $result = $this->mysqli->query("SELECT DATABASE()");
        $this->mysqli->query($sql);
    }


    public function executeMulti($sql, $type, $data)
    {
        $stmt = $this->mysqli->prepare($sql);

        $first = '';
        $second = '';

        $stmt->bind_param($type, $first, $second);

        foreach ($data as $second => $first) {
            $stmt->execute();
        }

        $stmt->close();
    }

    public function search($sql, $params = [])
    {
        $stmt = $this->mysqli->prepare($sql);

        $stmt->bind_param(...$params);

        $stmt->execute();

        /* 値を取得します */
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function catchInsertId()
    {
        return $this->mysqli->insert_id;
    }

    public function searchOne($sql, $params = [])
    {
        $stmt = $this->mysqli->prepare($sql);

        $stmt->bind_param(...$params);

        $stmt->execute();

        /* 値を取得します */
        return $stmt->get_result()->fetch_assoc();
    }


    public function createTables()
    {
        $this->mysqli->query('SET foreign_key_checks = 0');

$this->mysqli->query('DROP TABLE IF EXISTS categories');
$this->mysqli->query('DROP TABLE IF EXISTS customers');
$this->mysqli->query('DROP TABLE IF EXISTS employees');
$this->mysqli->query('DROP TABLE IF EXISTS products');
$this->mysqli->query('DROP TABLE IF EXISTS purchase_contracts');
$this->mysqli->query('DROP TABLE IF EXISTS purchase_products');
$this->mysqli->query('DROP TABLE IF EXISTS sales_contracts');
$this->mysqli->query('DROP TABLE IF EXISTS sales_products');
$this->mysqli->query('DROP TABLE IF EXISTS stocks');
$this->mysqli->query('DROP TABLE IF EXISTS suppliers');


$categoriesCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE KEY,
    index(category_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$this->mysqli->query($categoriesCreateTableSql);


$customersCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT NOT NULL PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    index(customer_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$this->mysqli->query($customersCreateTableSql);


$productsCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL UNIQUE KEY,
    list_price INT NOT NULL,
    category_id INT NOT NULL,
    index(category_id),
    FOREIGN KEY(category_id)
    REFERENCES categories(category_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$this->mysqli->query($productsCreateTableSql);


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

$this->mysqli->query($purchaseContractsCreateTableSql);


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

$this->mysqli->query($purchaseProductsCreateTableSql);


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

$this->mysqli->query($salesContractsCreateTableSql);


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

$this->mysqli->query($salesProductsCreateTableSql);


$stocksCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS stocks (
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    index(product_id),
    FOREIGN KEY(product_id)
    REFERENCES products(product_id) ON DELETE cascade
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$this->mysqli->query($stocksCreateTableSql);


$suppliersCreateTableSql = <<<EOT
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT NOT NULL PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    index(supplier_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

$this->mysqli->query($suppliersCreateTableSql);

$this->mysqli->query('SET foreign_key_checks = 1');

    }
}
