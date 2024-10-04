<?php

class Company extends DatabaseModel
{
    public function fetchCompany()
    {
        return $this->fetchAll('SELECT company_id, company_name FROM companies WHERE CHAR_LENGTH(company_id) = 4');
    }

    public function insert($company)
    {
        $this->execute('INSERT INTO companies (company_name, company_id) VALUES (?, ?)', ['si', $company['company_name'], $company['company_id']]);
    }

    public function delete($companyId)
    {
        $this->execute('DELETE FROM companies WHERE company_id = ?', ['i', $companyId]);
    }

    public function update($company)
    {
        $this->execute('UPDATE companies SET company_name = ? WHERE company_id = ?', ['si', $company['company_name'], $company['company_id']]);
    }

        public function createDatabase($companyId)
    {
        $this->querySet('CREATE DATABASE IF NOT EXISTS ' . $companyId);
    }

        public function dropDatabase($companyId)
    {
        $this->querySet('DROP DATABASE IF EXISTS ' . $companyId);
    }

        public function createTable()
    {
        $this->createTables();
    }

}
