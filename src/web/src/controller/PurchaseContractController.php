<?php

class purchaseContractController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("purchases");

        $userId = '';

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["purchaseIncrease"])) {
            unset($_SESSION["purchaseIncrease"]);
        }

        if (isset($_SESSION["purchaseEditing"])) {
            unset($_SESSION["purchaseEditing"]);
        }

        $table = '';

        if (isset($_SESSION['now_user_id'])) {
            $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlSuppliers = $this->databaseManager->get('Supplier', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listSuppliers = $sqlSuppliers->fetchAllSupplier();

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlPurchaseContract = $this->databaseManager->get('PurchaseContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listPurchaseContract = $sqlPurchaseContract->fetchAllPurchaseContract();
        // 表にデータを挿入
        $this->convert->convertJson($listPurchaseContract, 'purchaseContract');

        return $this->render([
            'title' => '仕入契約の登録',
            'errors' => [],
            'table' => $table,
            'suppliers' => $listSuppliers,
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("purchases");

        $errors['increase'] = [];

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["purchaseEditing"])) {
            unset($_SESSION["purchaseEditing"]);
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
        $sqlSuppliers = $this->databaseManager->get('Supplier', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listSuppliers = $sqlSuppliers->fetchAllSupplier();

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlPurchaseContract = $this->databaseManager->get('PurchaseContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listPurchaseContract = $sqlPurchaseContract->fetchAllPurchaseContract();

        if (!isset($_POST['supplier_name'])) {
            throw new HttpNotFoundException();
        }

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($_POST['supplier_name'], '@')) {
            $supplier['supplier_id'] = strstr($_POST['supplier_name'], '@', true);
            $supplier['supplier_name'] = substr(strstr($_POST['supplier_name'], '@', false), 1);
            $errors['increase'] = $errors['increase'] + $this->validate->supplierValidate($supplier, $listSuppliers, 'select');
        } else {
            $errors['increase']['supplier_name'] = '選択肢から選んでください';
        }

        if (isset($_POST['purchase_id']) && ($_POST['purchase_id'] == ! "")) {
            $contract = [
                'contract_id' => trim($_POST['purchase_id']),
                'supplier_id' => $supplier['supplier_id'],
                'supplier_name' => $supplier['supplier_name'],
                'contract_type' => 'purchase',
            ];
            $errors['increase'] = $errors['increase'] + $this->validate->contractValidate($contract, $listPurchaseContract, 'increase');
            $selector = 'disabled';
        } else {
            $errors['increase']['purchase_id'] = '契約番号を入力して下さい';
        }

        // カテゴリー選択フォーム用データ取り出し
        $listPurchaseContract = $sqlPurchaseContract->fetchAllPurchaseContract();
        // 表にデータを挿入
        $this->convert->convertJson($listPurchaseContract, 'purchaseContract');

        $increaseFieldset = '';

        if (!count($errors['increase'])) {
            $sqlPurchaseContract->insert($contract);
            $_SESSION['purchaseIncrease'] = $contract;
            $actionName = $this->actionName; //テーブルのID指定
            $controllerName = $this->controllerName; //submitの遷移先情報用
            $tableOutput = $this->tableRender($actionName, $controllerName, $contract, $userId, $token);
            $table = $tableOutput['table'];
            $increaseFieldset = 'disabled';
        }

        return $this->render([
            'title' => '仕入契約の登録',
            'errors' => $errors,
            'table' => $table,
            'suppliers' => $listSuppliers,
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

        $token = $this->securityCheck("purchases");

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["purchaseIncrease"])) {
            $userId = $_SESSION["now_user_id"];
            $contract = $_SESSION["purchaseIncrease"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["purchaseEditing"])) {
            unset($_SESSION["purchaseEditing"]);
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
            $_SESSION["purchaseIncrease"]['product'] = $tableOutput['product'];
        }

        return $this->render([
            'title' => '仕入契約の登録',
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

        $token = $this->securityCheck("purchases");

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["purchaseIncrease"])) {
            unset($_SESSION["purchaseIncrease"]);
        }

        if (isset($_SESSION['now_user_id'])) {
            $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlSuppliers = $this->databaseManager->get('Supplier', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listSuppliers = $sqlSuppliers->fetchAllSupplier();

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlPurchaseContracts = $this->databaseManager->get('PurchaseContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listPurchaseContracts = $sqlPurchaseContracts->fetchAllPurchaseContract();

        if (isset($_POST['search'])) {
            $editingSearch = $this->editingSearch($listPurchaseContracts, $listSuppliers);
            extract($editingSearch);
        } elseif (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($listPurchaseContracts, $userId, $token, $companyId);
            extract($editingSelect);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($listPurchaseContracts, $sqlPurchaseContracts, $companyId);
            extract($editingDelete);
        } else {
            $editingElse = $this->editingElse();
            extract($editingElse);
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlPurchaseContracts = $this->databaseManager->get('PurchaseContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listPurchaseContracts = $sqlPurchaseContracts->fetchAllPurchaseContract();
        // 表にデータを挿入
        $this->convert->convertJson($listPurchaseContracts, 'purchaseContract');

        return $this->render([
            'title' => '仕入契約の編集',
            'errors' => $errors,
            'table' => $table,
            'editingSession' => $editingSession,
            'editingContract' => $contract,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSelectFieldset' => $editingSelectFieldset,
            'suppliers' => $listSuppliers,
            'contracts' => $listPurchaseContracts,
            'selector' => $selector,
            'userId' => $userId,
            'taxRate' => $this->taxRate,
            'token' => $token,
        ], 'index');
    }

    public function editingTable()
    {

        session_start();

        $token = $this->securityCheck("purchases");

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["purchaseEditing"])) {
            $userId = $_SESSION["now_user_id"];
            $contract = $_SESSION["purchaseEditing"];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION["purchaseIncrease"])) {
            unset($_SESSION["purchaseIncrease"]);
        }

        if (isset($_POST)) {
            $postData = $_POST;
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
            $_SESSION["purchaseEditing"]['product'] = $tableOutput['product'];
        }

        return $this->render([
            'title' => '仕入契約の編集',
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

    private function editingSearch($listPurchaseContracts, $listSuppliers)
    {
        $contract = [];
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];

        if (!isset($_POST['supplier_name'])) {
            throw new HttpNotFoundException();
        }

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($_POST['supplier_name'], '@')) {
            $contract['supplier_id'] = (int)strstr($_POST['supplier_name'], '@', true);
            $contract['supplier_name'] = substr(strstr($_POST['supplier_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->supplierValidate($contract, $listSuppliers, 'select');
            $errors['editing'] = $errors['editing'] + $this->validate->contractValidate($contract, $listPurchaseContracts, 'search');
        } else {
            $errors['editing']['supplier_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $selector = 'disabled';
            $_SESSION['purchaseEditing'] = $contract;

            // フォーム内の契約ID欄用に選択業社の契約を抽出し変数に入れる
            foreach ($listPurchaseContracts as $listPurchaseContract) {
                if ($contract['supplier_id'] === $listPurchaseContract['supplier_id']) {
                    $listPurchaseContract['contract_id'] = $listPurchaseContract['purchase_contract_id'];
                    $checkedPurchaseContracts[] = $listPurchaseContract;
                }
            }
            $listPurchaseContracts = $checkedPurchaseContracts;
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
            'listPurchaseContracts' => $listPurchaseContracts,
        ];
    }

    private function editingSelect($listPurchaseContracts, $userId, $token, $companyId)
    {
        $contract = [];
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];

        if (isset($_SESSION['purchaseEditing'])) {
            $contract = $_SESSION['purchaseEditing'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['contract_id'])) {
            $contract['contract_id'] = (int)trim($_POST['contract_id']);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlPurchaseContracts = $this->databaseManager->get('PurchaseContract', $companyId);
        // カテゴリー選択フォーム用データ取り出し
        $listPurchaseContracts = $sqlPurchaseContracts->fetchAllPurchaseContract();

        foreach ($listPurchaseContracts as $listPurchaseContract) {
            if ($contract['supplier_id'] === $listPurchaseContract['supplier_id']) {
                $listPurchaseContract['contract_id'] = $listPurchaseContract['purchase_contract_id'];
                $checkedPurchaseContracts[] = $listPurchaseContract;
            }
        }

        $listPurchaseContracts = $checkedPurchaseContracts;
        $errors['editing'] = $errors['editing'] + $this->validate->contractValidate($contract, $listPurchaseContracts, 'select');

        if (!count($errors['editing'])) {
            $contract['contract_type'] = 'purchase';
            $_SESSION['purchaseEditing'] = $contract;
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
            'listPurchaseContracts' => $listPurchaseContracts,
        ];
    }

    private function editingDelete($listPurchaseContracts, $sqlPurchaseContracts, $companyId)
    {
        $contract = [];
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $editingSession = 'show';
        $errors['editing'] = [];


        if (isset($_SESSION['purchaseEditing'])) {
            $contract = $_SESSION['purchaseEditing'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['contract_id'])) {
            if (isset($_POST['contract_id'])) {
                $contract['contract_id'] = trim($_POST['contract_id']);
            } else {
                throw new HttpNotFoundException();
            }

            // 選択業社に既存の契約が存在するかのバリデーション
            foreach ($listPurchaseContracts as $listPurchaseContract) {
                if ($contract['supplier_id'] === $listPurchaseContract['supplier_id']) {
                    $listPurchaseContract['contract_id'] = $listPurchaseContract['purchase_contract_id'];
                    $checkedPurchaseContracts[] = $listPurchaseContract;
                }
            }
            $listPurchaseContracts = $checkedPurchaseContracts;
            $errors['editing'] = $errors['editing'] + $this->validate->contractValidate($contract, $listPurchaseContracts, 'select');
        } else {
            $errors['editing']['supplier_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $contract['purchase_contract_id'] = $contract['contract_id'];

            // 契約の削除に伴う商品在庫数の調整
            $sqlPurchaseProducts = $this->databaseManager->get('PurchaseProduct', $companyId);
            $listPurchaseProducts = $sqlPurchaseProducts->fetchPurchaseProduct($contract);
            foreach ($listPurchaseProducts as $listPurchaseProduct) {
                $purchaseStocks[$listPurchaseProduct['product_id']] = $listPurchaseProduct['number'];
            }
            $sqlStock = $this->databaseManager->get('Stock', $companyId);
            $sqlStock->decreaseMulti($purchaseStocks);

            unset($_SESSION["purchaseEditing"]);
            $editingSelectFieldset = 'disabled';
            $editingSession = '';
            $sqlPurchaseContracts->delete($contract);
            $contract = [];
        }

        return [
            'contract' => $contract,
            'table' => $table,
            'selector' => $selector,
            'errors' => $errors,
            'editingSelectFieldset' => $editingSelectFieldset,
            'editingSearchFieldset' => $editingSearchFieldset,
            'editingSession' => $editingSession,
            'listPurchaseContracts' => $listPurchaseContracts,
        ];
    }

    private function editingElse()
    {
        $contract = [];
        $table = '';
        $selector = '';
        $editingSelectFieldset = 'disabled';
        $editingSearchFieldset = '';
        $errors['editing'] = [];

        $editingSession = '';
        if (isset($_SESSION["purchaseEditing"])) {
            unset($_SESSION["purchaseEditing"]);
        }

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
