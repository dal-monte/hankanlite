<?php

class Customer extends DatabaseModel
{
    public function fetchAllCustomer()
    {
        return $this->fetchAll('SELECT customer_id, name FROM customers');
    }

    public function insert($customer)
    {
        $this->execute('INSERT INTO customers (name, customer_id) VALUES (?, ?)', ['si', $customer['customer_name'], $customer['customer_id']]);
    }

    public function delete($customer)
    {
        $this->execute('DELETE FROM customers WHERE customer_id = ?', ['i', $customer['customer_id']]);
    }

    public function update($customer)
    {
        $this->execute('UPDATE customers SET name = ? WHERE customer_id = ?', ['si', $customer['customer_name'], $customer['customer_id']]);
    }
}
