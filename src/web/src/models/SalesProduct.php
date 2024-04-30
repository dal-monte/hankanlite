<?php

class SalesProduct extends DatabaseModel
{
    public function fetchSalesProduct($contract)
    {
        return $this->search('SELECT p.name AS product_name, sp.product_id, c.name AS category_name, c.category_id, sp.sales_price AS price, sp.number, sp.sales_price * sp.number AS subtotal, p.list_price, s.quantity, sp.sales_contract_id FROM sales_products AS sp JOIN products AS p ON sp.product_id = p.product_id JOIN categories AS c ON p.category_id = c.category_id JOIN stocks AS s ON sp.product_id = s.product_id WHERE sp.sales_contract_id = ?', ["i", $contract['sales_contract_id']]);
    }

    public function insert($product)
    {
        $this->execute('INSERT INTO sales_products (sales_contract_id, product_id, number, sales_price)  VALUES (?, ?, ?, ?)', ['iiii', $product['sales_contract_id'], $product['product_id'], $product['number'], $product['price']]);
    }

    public function delete($product)
    {
        $this->execute('DELETE FROM sales_products WHERE sales_contract_id = ? AND product_id = ?', ['ii', $product['sales_contract_id'], $product['product_id']]);
    }

    public function update($product)
    {
        $this->execute('UPDATE sales_products SET number = ?, sales_price = ? WHERE sales_contract_id = ? AND product_id = ?', ['iiii', $product['number'], $product['price'], $product['sales_contract_id'], $product['product_id']]);
    }

    public function searchContract($productId)
    {
        return $this->search('SELECT sales_contract_id FROM sales_products WHERE product_id = ?', ["i", $productId]);
    }
}
