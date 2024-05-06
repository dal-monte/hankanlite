<?php

class Category extends DatabaseModel
{
    public function fetchAllCategory()
    {
        return $this->fetchAll('SELECT category_id, category_name FROM categories');
    }

    public function insert($categoryName)
    {
        $this->execute('INSERT INTO categories (category_name) VALUES (?)', ['s', $categoryName]);
    }

    public function delete($category)
    {
        $this->execute('DELETE FROM categories WHERE category_id = ?', ['i', $category['category_id']]);
    }

    public function update($category)
    {
        $this->execute('UPDATE categories SET category_name = ? WHERE category_id = ?', ['si', $category['category_name'], $category['category_id']]);
    }
}
