<?php

class SqlCommand
{
    public $categoriesCreate = <<<EOT
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE KEY,
    index(category_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

    public $customersCreate = <<<EOT
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT NOT NULL PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    index(customer_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

    public $productsCreate = <<<EOT
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

    public $purchaseContractsCreate = <<<EOT
CREATE TABLE IF NOT EXISTS purchase_contracts (
    supplier_id INT NOT NULL,
    purchase_contract_id INT NOT NULL PRIMARY KEY,
    index(supplier_id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(supplier_id)
    REFERENCES suppliers(supplier_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

    public $purchaseProductsCreate = <<<EOT
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

    public $salesContractsCreate = <<<EOT
CREATE TABLE IF NOT EXISTS sales_contracts (
    customer_id INT NOT NULL,
    sales_contract_id INT NOT NULL PRIMARY KEY,
    index(customer_id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(customer_id)
    REFERENCES customers(customer_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

    public $salesProductsCreate = <<<EOT
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

    public $stocksCreate = <<<EOT
CREATE TABLE IF NOT EXISTS stocks (
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    index(product_id),
    FOREIGN KEY(product_id)
    REFERENCES products(product_id) ON DELETE cascade
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;

    public $suppliersCreate = <<<EOT
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT NOT NULL PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    index(supplier_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
EOT;
}
