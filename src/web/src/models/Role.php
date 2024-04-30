<?php

class Role extends DatabaseModel
{
    public function fetchRole()
    {
        return $this->fetchAll('SELECT role_name, role_id FROM roles WHERE role_id >= 1');
    }
}
