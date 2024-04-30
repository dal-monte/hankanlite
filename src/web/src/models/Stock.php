<?php

class Stock extends DatabaseModel
{
    public function insert($productId)
    {
        $this->execute('INSERT INTO stocks (product_id, quantity) VALUES (?, ?)', ['ii', $productId, '0']);
    }

    public function increase($product)
    {
        $this->execute('UPDATE stocks SET quantity = quantity + ? WHERE product_id = ?', ['ii', $product['number'], $product['product_id']]);
    }

    public function increaseMulti($stocks)
    {
        $this->executeMulti('UPDATE stocks SET quantity = quantity + ? WHERE product_id = ?', 'ii', $stocks);
    }

    public function decrease($product)
    {
        $this->execute('UPDATE stocks SET quantity = quantity - ? WHERE product_id = ?', ['ii', $product['number'], $product['product_id']]);
    }

    public function decreaseMulti($stocks)
    {
        $this->executeMulti('UPDATE stocks SET quantity = quantity - ? WHERE product_id = ?', 'ii', $stocks);
    }
}
