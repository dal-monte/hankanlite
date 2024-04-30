<?php

class Product extends DatabaseModel
{
    public function fetchAllProduct()
    {
        return $this->fetchAll('SELECT p.product_id, p.name, p.list_price, c.name AS category_name, s.quantity, p.category_id FROM products AS p INNER JOIN categories AS c ON p.category_id = c.category_id INNER JOIN stocks AS s ON p.product_id = s.product_id');
    }

    public function insert($product)
    {
        $this->execute('INSERT INTO products (name, list_price, category_id)  VALUES (?, ?, ?)', ['sii', $product['product_name'], $product['list_price'], $product['category_id']]);
        return $this->catchInsertId();
    }

    public function delete($product)
    {
        $this->execute('DELETE FROM products WHERE product_id = ?', ['i', $product['product_id']]);
    }

    public function update($product)
    {
        $this->execute('UPDATE products SET name = ?, list_price = ?, category_id = ? WHERE product_id = ?', ['siii', $product['name'], $product['list_price'], $product['category_id'], $product['product_id']]);
    }

    public function searchProducts($categoryId)
    {
        return $this->search('SELECT product_id FROM products WHERE category_id = (?)', ["i", $categoryId]);
    }
}
