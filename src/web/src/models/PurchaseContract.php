<?php

class PurchaseContract extends DatabaseModel
{
    public function fetchAllPurchaseContract()
    {
        return $this->fetchAll("SELECT DATE_FORMAT(CONVERT_TZ(pc.created_at,'+00:00','+09:00'), '%Y年%m月%d日') AS created_at, pc.purchase_contract_id, pc.supplier_id, s.supplier_name FROM purchase_contracts AS pc JOIN suppliers AS s ON pc.supplier_id = s.supplier_id");
    }

    public function insert($contract)
    {
        $this->execute('INSERT INTO purchase_contracts (purchase_contract_id, supplier_id) VALUES (?, ?)', ['ii', $contract['contract_id'], $contract['supplier_id']]);
    }

    public function delete($contract)
    {
        $this->execute('DELETE FROM purchase_contracts WHERE purchase_contract_id = ?', ['i', $contract['contract_id']]);
    }

    public function update($contract)
    {
        $this->execute('UPDATE purchase_contracts SET supplier_id = ? WHERE purchase_contract_id = ?', ['si', $contract['supplier_id'], $contract['contract_id']]);
    }
}
