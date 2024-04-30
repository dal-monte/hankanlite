<?php

class SalesContract extends DatabaseModel
{
    public function fetchAllSalesContract()
    {
        return $this->fetchAll("SELECT DATE_FORMAT(CONVERT_TZ(sc.created_at,'+00:00','+09:00'), '%Y年%m月%d日') AS created_at, sc.sales_contract_id, sc.customer_id, s.name AS customer_name FROM sales_contracts AS sc JOIN customers AS s ON sc.customer_id = s.customer_id");
    }

    public function insert($contract)
    {
        $this->execute('INSERT INTO sales_contracts (sales_contract_id, customer_id) VALUES (?, ?)', ['ii', $contract['contract_id'], $contract['customer_id']]);
    }

    public function delete($contract)
    {
        $this->execute('DELETE FROM sales_contracts WHERE sales_contract_id = ?', ['i', $contract['contract_id']]);
    }

    public function update($contract)
    {
        $this->execute('UPDATE sales_contracts SET customer_id = ? WHERE sales_contract_id = ?', ['si', $contract['customer_id'], $contract['contract_id']]);
    }
}
