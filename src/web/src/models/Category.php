<?php

class Category extends DatabaseModel
{
    public function fetchAllCategory()
    {
        return $this->fetchAll('SELECT category_id, name FROM categories');
    }

    public function insert($name)
    {
        $this->execute('INSERT INTO categories (name) VALUES (?)', ['s', $name]);
    }

    public function delete($category)
    {
        $this->execute('DELETE FROM categories WHERE category_id = ?', ['i', $category['category_id']]);
    }

    public function update($category)
    {
        $this->execute('UPDATE categories SET name = ? WHERE category_id = ?', ['si', $category['category_name'], $category['category_id']]);
    }
}
