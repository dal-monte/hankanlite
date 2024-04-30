<?php
class Convert
{
    public function convertJson($list, $controllerName, $userId = null)
    {
        if (is_null($userId)) {
            $table['data'] = $list;
            $table = json_encode($table, JSON_UNESCAPED_UNICODE);
            file_put_contents(__DIR__ . '/../assets/json/' . $controllerName . '.json', $table);
        } else {
            $table[$userId] = $list;
            $jsonContractProductTable = file_get_contents(__DIR__ . '/../assets/json/' . $controllerName . '.json');
            $contractProductTable = json_decode($jsonContractProductTable, true);

            // file_get_contentsは、getするデータが存在しない場合falseを返すため、その判定をする。データが存在しない場合はjsonファイルを新規作成する
            if ($jsonContractProductTable) {
                $contractProductTable[$userId] = $table[$userId];
                $addTable = json_encode($contractProductTable, JSON_UNESCAPED_UNICODE);
                file_put_contents(__DIR__ . '/../assets/json/' . $controllerName . '.json', $addTable);
            } else {
                $table[$userId] = $list;
                $table = json_encode($table, JSON_UNESCAPED_UNICODE);
                file_put_contents(__DIR__ . '/../assets/json/' . $controllerName . '.json', $table);
            }
        }
    }
}
