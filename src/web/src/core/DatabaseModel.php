<?php

class DatabaseModel
{
    protected $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function fetchAll($sql)
    {
        $result = $this->mysqli->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->mysqli->prepare($sql);

        $stmt->bind_param(...$params);

        $stmt->execute();
        $stmt->close();
    }

    public function executeMulti($sql, $type, $data)
    {
        $stmt = $this->mysqli->prepare($sql);

        $first = '';
        $second = '';

        $stmt->bind_param($type, $first, $second);

        foreach ($data as $second => $first) {
            $stmt->execute();
        }

        $stmt->close();
    }

    public function search($sql, $params = [])
    {
        $stmt = $this->mysqli->prepare($sql);

        $stmt->bind_param(...$params);

        $stmt->execute();

        /* 値を取得します */
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function catchInsertId()
    {
        return $this->mysqli->insert_id;
    }

    public function searchOne($sql, $params = [])
    {
        $stmt = $this->mysqli->prepare($sql);

        $stmt->bind_param(...$params);

        $stmt->execute();

        /* 値を取得します */
        return $stmt->get_result()->fetch_assoc();
    }
}
