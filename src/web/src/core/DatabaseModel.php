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

        $this->mysqli->query($this->sqlCommand->categoriesCreate);
        $this->mysqli->query($this->sqlCommand->customersCreate);
        $this->mysqli->query($this->sqlCommand->productsCreate);
        $this->mysqli->query($this->sqlCommand->purchaseContractsCreate);
        $this->mysqli->query($this->sqlCommand->purchaseProductsCreate);
        $this->mysqli->query($this->sqlCommand->salesContractsCreate);
        $this->mysqli->query($this->sqlCommand->salesProductsCreate);
        $this->mysqli->query($this->sqlCommand->stocksCreate);
        $this->mysqli->query($this->sqlCommand->suppliersCreate);

        $this->mysqli->query('SET foreign_key_checks = 1');
    }
}
