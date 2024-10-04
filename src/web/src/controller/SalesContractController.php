<?php

class SalesContractController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("sales");

        $userId = '';

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["salesIncrease"])) {
            unset($_SESSION["salesIncrease"]);
        }

        if (isset($_SESSION["salesEditing"])) {
            unset($_SESSION["salesEditing"]);
        }

        $table = '';

        if (isset($_SESSION['now_user_id'])) {
        $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCustomers = $this->databaseManager->get('Customer', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listCustomers = $sqlCustomers->fetchAllCustomer();

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlSalesContracts = $this->databaseManager->get('SalesContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listSalesContracts = $sqlSalesContracts->fetchAllSalesContract();
        // 表にデータを挿入
        $this->convert->convertJson($listSalesContracts, 'salesContract');

        return $this->render([
            'title' => '販売契約の登録',
            'errors' => [],
            'table' => $table,
            'customers' => $listCustomers,
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("sales");

        $errors['increase'] = [];

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["salesEditing"])) {
            unset($_SESSION["salesEditing"]);
        }

        $table = '';
        $selector = '';
        $contract = null;

        if (isset($_SESSION['now_user_id'])) {
        $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCustomers = $this->databaseManager->get('Customer', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listCustomers = $sqlCustomers->fetchAllCustomer();

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlSalesContracts = $this->databaseManager->get('SalesContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listSalesContracts = $sqlSalesContracts->fetchAllSalesContract();

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (!isset($_POST['customer_name'])) {
            throw new HttpNotFoundException();
        }

        if (strpos($_POST['customer_name'], '@')) {
            $customer['customer_id'] = strstr($_POST['customer_name'], '@', true);
            $customer['customer_name'] = substr(strstr($_POST['customer_name'], '@', false), 1);
            $errors['increase'] = $errors['increase'] + $this->validate->customerValidate($customer, $listCustomers, 'select');
        } else {
            $errors['increase']['customer_name'] = '選択肢から選んでください';
        }

        if (isset($_POST['sales_id']) && $_POST['sales_id'] ==! "") {
            $contract = [
                'contract_id' => trim($_POST['sales_id']),
                'customer_id' => $customer['customer_id'],
                'customer_name' => $customer['customer_name'],
                'contract_type' => 'sales',
            ];
            $errors['increase'] = $errors['increase'] + $this->validate->contractValidate($contract, $listSalesContracts, 'increase');
            $selector = 'disabled';
        } else {
            $errors['increase']['purchase_id'] = '契約番号を入力して下さい';
        }

        // カテゴリー選択フォーム用データ取り出し
        $listSalesContracts = $sqlSalesContracts->fetchAllSalesContract();
        // 表にデータを挿入
        $this->convert->convertJson($listSalesContracts, 'salesContract');

        $increaseFieldset = '';

        if (!count($errors['increase'])) {
            $sqlSalesContracts->insert($contract);
            $_SESSION['salesIncrease'] = $contract;
            $actionName = $this->actionName; //テーブルのID指定
            $controllerName = $this->controllerName; //submitの遷移先情報用
            $tableOutput = $this->tableRender($actionName, $controllerName, $contract, $userId, $token);
            $table = $tableOutput['table'];
            $increaseFieldset = 'disabled';
        }

        return $this->render([
            'title' => '販売契約の登録',
            'errors' => $errors,
            'table' => $table,
            'customers' => $listCustomers,
            'selector' => $selector,
            'increaseSession' => 'show',
            'increaseContract' => $contract,
            'increaseFieldset' => $increaseFieldset,
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ], 'index');
    }

    public function increaseTable()
    {
        session_start();

        $token = $this->securityCheck("sales");

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["salesIncrease"])) {
            $userId = $_SESSION["now_user_id"];
            $contract = $_SESSION["salesIncrease"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["salesEditing"])) {
            unset($_SESSION["salesEditing"]);
        }

        $increaseFieldset = 'disabled';

        if (isset($_POST)) {
            $postData = $_POST;
        } else {
            throw new HttpNotFoundException();
        }

        $actionName = $this->actionName; //テーブルのID指定
        if (substr($actionName, -5) === 'Table') {  //テーブル内のIDから来るとactionNameの後方にTableが付いているため取り除く
            $actionName = substr($actionName, 0, -5);
        }
        $controllerName = $this->controllerName; //submitの遷移先情報用

        $tableOutput = $this->tableRender($actionName, $controllerName, $contract, $userId, $token, $postData);
        $table = $tableOutput['table'];
        if (isset($tableOutput['product'])) {
            $_SESSION["salesIncrease"]['product'] = $tableOutput['product'];
        }

        return $this->render([
            'title' => '販売契約の登録',
            'errors' => [],
            'table' => $table,
            'increaseSession' => 'show',
            'increaseContract' => $contract,
            'increaseFieldset' => $increaseFieldset,
            'selector' => 'disabled',
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ], 'index');
    }

    public function editing()
    {
        session_start();

        $token = $this->securityCheck("sales");

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["salesIncrease"])) {
            unset($_SESSION["salesIncrease"]);
        }

        $contract = [];
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';

        if (isset($_SESSION['now_user_id'])) {
        $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCustomers = $this->databaseManager->get('Customer', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listCustomers = $sqlCustomers->fetchAllCustomer();

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlSalesContracts = $this->databaseManager->get('SalesContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listSalesContracts = $sqlSalesContracts->fetchAllSalesContract();
        // 表にデータを挿入
        $this->convert->convertJson($listSalesContracts, 'salesContract');

        if (isset($_POST['search'])) {
            $editingSearch = $this->editingSearch($listCustomers, $listSalesContracts);
            extract($editingSearch);
        } elseif (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($listSalesContracts, $userId, $token);
            extract($editingSelect);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($listSalesContracts, $sqlSalesContracts, $companyId);
            extract($editingDelete);
        } else {
            $editingElse   = $this->editingElse();
            extract($editingElse);
        }

        return $this->render([
            'title' => '販売契約の編集',
            'errors' => $errors,
            'table' => $table,
            'editingSession' => $editingSession,
            'editingContract' => $contract,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSelectFieldset' => $editingSelectFieldset,
            'customers' => $listCustomers,
            'contracts' => $listSalesContracts,
            'selector' => $selector,
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ], 'index');
    }

    public function editingTable()
    {
        session_start();

        $token = $this->securityCheck("sales");

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["salesEditing"])) {
            $userId = $_SESSION["now_user_id"];
            $contract = $_SESSION["salesEditing"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["salesIncrease"])) {
            unset($_SESSION["salesIncrease"]);
        }

        if (isset($_POST)) {
            $postData = $_POST;
        } else {
            throw new HttpNotFoundException();
        }
        $editingSearchFieldset = 'disabled';

        $actionName = $this->actionName; //テーブルのID指定
        if (substr($actionName, -5) === 'Table') {  //テーブル内のIDから来るとactionNameの後方にTableが付いているため取り除く
            $actionName = substr($actionName, 0, -5);
        }
        $controllerName = $this->controllerName; //submitの遷移先情報用

        $tableOutput = $this->tableRender($actionName, $controllerName, $contract, $userId, $token, $postData);
        $table = $tableOutput['table'];
        if (isset($tableOutput['product'])) {
            $_SESSION["salesEditing"]['product'] = $tableOutput['product'];
        }

        return $this->render([
            'title' => '販売契約の編集',
            'errors' => [],
            'table' => $table,
            'editingSession' => 'show',
            'editingContract' => $contract,
            'editingSearchFieldset' => $editingSearchFieldset,
            'selector' => 'disabled',
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ], 'index');
    }

    private function editingSearch($listCustomers, $listSalesContracts)
    {
        $contract = [];
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];

        if (!isset($_POST['customer_name'])) {
            throw new HttpNotFoundException();
        }

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($_POST['customer_name'], '@')) {
            $contract['customer_id'] = strstr($_POST['customer_name'], '@', true);
            $contract['customer_name'] = substr(strstr($_POST['customer_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->customerValidate($contract, $listCustomers, 'select');
            $errors['editing'] = $errors['editing'] + $this->validate->contractValidate($contract, $listSalesContracts, 'search');
        } else {
            $errors['editing']['customer_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $selector = 'disabled';
            $_SESSION['salesEditing'] = $contract;

            // フォーム内の契約ID欄用に選択業社の契約を抽出し変数に入れる
            foreach ($listSalesContracts as $listSalesContract) {
                if ($contract['customer_id'] === $listSalesContract['customer_id']) {
                    $listSalesContract['contract_id'] = $listSalesContract['sales_contract_id'];
                    $checkedSalesContracts[] = $listSalesContract;
                }
            }
            $listSalesContracts = $checkedSalesContracts;
            $editingSearchFieldset = 'disabled';
            $editingSelectFieldset = '';
        }

        return [
            'contract' => $contract,
            'table' => $table,
            'selector' => $selector,
            'errors' => $errors,
            'editingSelectFieldset' => $editingSelectFieldset,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSession' => $editingSession,
            'listSalesContracts' => $listSalesContracts,
        ];
    }

    private function editingSelect($listSalesContracts, $userId, $token)
    {
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];

        if (isset($_SESSION['salesEditing'])) {
            $contract = $_SESSION['salesEditing'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['contract_id'])) {
            $contract['contract_id'] = $_POST['contract_id'];
            foreach ($listSalesContracts as $listSalesContracts) {
                if ($contract['customer_id'] === $listSalesContracts['customer_id']) {
                    $listSalesContracts['contract_id'] = $listSalesContracts['sales_contract_id'];
                    $checkedSalesContracts[] = $listSalesContracts;
                }
            }
            $listSalesContracts = $checkedSalesContracts;
            $errors['editing'] = $errors['editing'] + $this->validate->contractValidate($contract, $listSalesContracts, 'select');
        } else {
            throw new HttpNotFoundException();
        }

        if (!count($errors['editing'])) {
            $contract['contract_type'] = 'sales';
            $_SESSION['salesEditing'] = $contract;
            $actionName = $this->actionName; //テーブルのID指定
            $controllerName = $this->controllerName; //submitの遷移先情報用
            $tableOutput = $this->tableRender($actionName, $controllerName, $contract, $userId, $token);
            $table = $tableOutput['table'];
            $selector = 'disabled';
            $editingSearchFieldset = 'disabled';
        }

        return [
            'contract' => $contract,
            'table' => $table,
            'selector' => $selector,
            'errors' => $errors,
            'editingSelectFieldset' => $editingSelectFieldset,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSession' => $editingSession,
            'listSalesContracts' => $listSalesContracts,
        ];
    }

    private function editingDelete($listSalesContracts, $sqlSalesContracts, $companyId)
    {
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];

        if (isset($_SESSION['salesEditing'])) {
            $contract = $_SESSION['salesEditing'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['contract_id'])) {
            $contract['contract_id'] = $_POST['contract_id'];
        } else {
            throw new HttpNotFoundException();
        }

        // 選択業社に既存の契約が存在するかのバリデーション
        foreach ($listSalesContracts as $listSalesContracts) {
            if ($contract['customer_id'] === $listSalesContracts['customer_id']) {
                $listSalesContracts['contract_id'] = $listSalesContracts['sales_contract_id'];
                $checkedSalesContracts[] = $listSalesContracts;
            }
        }
        $listSalesContracts = $checkedSalesContracts;
        $errors['editing'] = $errors['editing'] + $this->validate->contractValidate($contract, $listSalesContracts, 'select');

        if (!count($errors['editing'])) {
            $contract['sales_contract_id'] = $contract['contract_id'];

            // 契約の削除に伴う商品在庫数の調整
            $sqlSalesProducts = $this->databaseManager->get('SalesProduct', $companyId);
            $listSalesProducts = $sqlSalesProducts->fetchSalesProduct($contract);
            foreach ($listSalesProducts as $listSalesProduct) {
                $salesStocks[$listSalesProduct['product_id']] = $listSalesProduct['number'];
            }
            $sqlStock = $this->databaseManager->get('Stock', $companyId);
            $sqlStock->increaseMulti($salesStocks);

            unset($_SESSION["salesEditing"]);
            $editingSelectFieldset = 'disabled';
            $editingSession = '';
            $sqlSalesContracts->delete($contract);
            $contract = [];
            $listSalesContracts = $sqlSalesContracts->fetchAllSalesContract();
            $this->convert->convertJson($listSalesContracts, 'salesContract');
            $editingSession = '';
        }

        return [
            'contract' => $contract,
            'table' => $table,
            'selector' => $selector,
            'errors' => $errors,
            'editingSelectFieldset' => $editingSelectFieldset,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSession' => $editingSession,
            'listSalesContracts' => $listSalesContracts,
        ];
    }

    private function editingElse()
    {
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];


        $editingSession = '';
        if (isset($_SESSION["salesEditing"])) {
            unset($_SESSION["salesEditing"]);
        }
        $contract = [];

        return [
            'contract' => $contract,
            'table' => $table,
            'selector' => $selector,
            'errors' => $errors,
            'editingSelectFieldset' => $editingSelectFieldset,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSession' => $editingSession,
        ];
    }
}
