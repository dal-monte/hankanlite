<?php

class PurchaseProduct extends DatabaseModel
{
    public function fetchPurchaseProduct($contract)
    {
        return $this->search('SELECT p.name AS product_name, pp.product_id, c.name AS category_name, c.category_id, pp.purchase_price AS price, pp.number, pp.purchase_price * pp.number AS subtotal, p.list_price, s.quantity, pp.purchase_contract_id FROM purchase_products AS pp JOIN products AS p ON pp.product_id = p.product_id JOIN categories AS c ON p.category_id = c.category_id JOIN stocks AS s ON pp.product_id = s.product_id WHERE pp.purchase_contract_id = ?', ["i", $contract['purchase_contract_id']]);
    }

    public function insert($product)
    {
        $this->execute('INSERT INTO purchase_products (purchase_contract_id, product_id, number, purchase_price)  VALUES (?, ?, ?, ?)', ['iiii', $product['purchase_contract_id'], $product['product_id'], $product['number'], $product['price']]);
    }

    public function delete($product)
    {
        $this->execute('DELETE FROM purchase_products WHERE purchase_contract_id = ? AND product_id = ?', ['ii', $product['purchase_contract_id'], $product['product_id']]);
    }

    public function update($product)
    {
        $this->execute('UPDATE purchase_products SET number = ?, purchase_price = ? WHERE purchase_contract_id = ? AND product_id = ?', ['iiii', $product['number'], $product['price'], $product['purchase_contract_id'], $product['product_id']]);
    }

    public function searchContract($productId)
    {
        return $this->search('SELECT purchase_contract_id FROM purchase_products WHERE product_id = ?', ["i", $productId]);
    }
}
