<?php

class DatabaseManager
{
    protected $mysqli;
    protected $models;

    protected $sqlCommand;

    public function connect($params, $sqlCommand)
    {
        $mysqli = new mysqli($params['hostname'], $params['username'], $params['password']);

        if ($mysqli->connect_error) {
            throw new RuntimeException('mysqli接続エラー:' . $mysqli->connect_error);
        }

        $this->mysqli = $mysqli;
        $this->sqlCommand = $sqlCommand;
    }

    public function get($modelName, $dbname=null)
    {
        if (isset($dbname)) {
            // データベースの選択
            $this->mysqli->select_db($dbname);
        }

        if (!isset($this->models[$modelName])) {
            /**
             * (例)$modelNameが'Product'の場合、
             * modelsディレクトリのProductクラスにmysqlログイン情報を渡してnewする
             */
            $model = new $modelName($this->mysqli, $this->sqlCommand);
            $this->models[$modelName] = $model;
        }
        return $this->models[$modelName];
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }
}
