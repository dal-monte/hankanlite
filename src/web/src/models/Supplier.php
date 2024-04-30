<?php

class Supplier extends DatabaseModel
{
    public function fetchAllSupplier()
    {
        return $this->fetchAll('SELECT supplier_id, name FROM suppliers');
    }

    public function insert($supplier)
    {
        $this->execute('INSERT INTO suppliers (name, supplier_id) VALUES (?, ?)', ['si', $supplier['supplier_name'], $supplier['supplier_id']]);
    }

    public function delete($supplier)
    {
        $this->execute('DELETE FROM suppliers WHERE supplier_id = ?', ['i', $supplier['supplier_id']]);
    }

    public function update($supplier)
    {
        $this->execute('UPDATE suppliers SET name = ? WHERE supplier_id = ?', ['si', $supplier['supplier_name'], $supplier['supplier_id']]);
    }
}
