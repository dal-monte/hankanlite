<?php

class User extends DatabaseModel
{
    public function fetchUser($checkCompanyUsers)
    {
        return $this->search('SELECT u.user_id, u.user_name, u.role_id, r.role_name AS role FROM users as u JOIN roles AS r ON u.role_id = r.role_id WHERE CHAR_LENGTH(user_id) = 8 AND u.user_id LIKE (?)', ['s', $checkCompanyUsers]);
    }

    public function insert($user)
    {
        $this->execute('INSERT INTO users (user_name, user_id, role_id, password) VALUES (?, ?, ?, ?)', ['siis', $user['user_name'], $user['user_id'], $user['role_id'], $user['password']]);
    }

    public function delete($user)
    {
        $this->execute('DELETE FROM users WHERE user_id = ?', ['i', $user['user_id']]);
    }

    public function deleteUsers($checkCompanyUsers)
    {
        $this->execute('DELETE FROM users WHERE CHAR_LENGTH(user_id) = 8 AND user_id LIKE (?)', ['s', $checkCompanyUsers]);
    }

    public function update($user)
    {
        $this->execute('UPDATE users SET user_name = ?,role_id = ? WHERE user_id = ?', ['sii', $user['user_name'], $user['role_id'], $user['user_id']]);
    }

    public function check($user_id)
    {
        return $this->searchOne('SELECT u.user_id, u.user_name, u.password, r.role FROM users AS u JOIN roles AS r ON u.role_id = r.role_id WHERE user_id = (?)', ["i", $user_id]);
    }
}
