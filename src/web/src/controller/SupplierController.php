<?php

class SupplierController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("purchases");

        if (isset($_SESSION['supplier_id'])) {
            unset($_SESSION['supplier_id']);
        }
        if (isset($_SESSION['supplier_name'])) {
            unset($_SESSION['supplier_name']);
        }

        $sqlSuppliers = $this->databaseManager->get('Supplier');
        // // mysqlのSuppliersテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listSuppliers = $sqlSuppliers->fetchAllSupplier();
        $this->convert->convertJson($listSuppliers, 'supplier');

        return $this->render([
            'title' => '顧客の登録',
            'errors' => [],
            'suppliers' => $listSuppliers,
            'token' => $token,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("purchases");

        $errors['increase'] = [];

        if (isset($_SESSION['supplier_id'])) {
            unset($_SESSION['supplier_id']);
        }
        if (isset($_SESSION['supplier_name'])) {
            unset($_SESSION['supplier_name']);
        }

        $increase = 'show';

        // modelsディレクトリのSupplierクラスをnewして$sqlSuppliersに渡す
        $sqlSuppliers = $this->databaseManager->get('Supplier');

        if (isset($_POST['supplier_id'])) {
            $supplier['supplier_id'] = trim($_POST['supplier_id']);
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['supplier_name'])) {
            $supplier['supplier_name'] = trim($_POST['supplier_name']);
        } else {
            throw new HttpNotFoundException();
        }

        $listSuppliers = $sqlSuppliers->fetchAllSupplier();
        $errors['increase'] = $errors['increase'] + $this->validate->supplierValidate($supplier, $listSuppliers, 'increase');

        if (!count($errors['increase'])) {
            $sqlSuppliers->insert($supplier);
            $supplier = [];
            $increase = '';
            $listSuppliers = $sqlSuppliers->fetchAllSupplier();
            $this->convert->convertJson($listSuppliers, 'supplier');
        }

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => $errors,
            'suppliers' => $listSuppliers,
            'supplier' => $supplier,
            'increase' => $increase,
            'token' => $token,
        ], 'index');
    }

    public function editing()
    {
        session_start();

        $token = $this->securityCheck("purchases");

        // modelsディレクトリのSupplierクラスをnewして$sqlSuppliersに渡す
        $sqlSuppliers = $this->databaseManager->get('Supplier');

        if (isset($_POST['supplier_name'])) {
            $postSupplier['supplier_name'] = trim($_POST['supplier_name']);
        } else {
            throw new HttpNotFoundException();
        }

        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listSuppliers = $sqlSuppliers->fetchAllSupplier();
        $this->convert->convertJson($listSuppliers, 'supplier');

        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        if (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($postSupplier, $listSuppliers);
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate($postSupplier, $sqlSuppliers);
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($postSupplier, $listSuppliers, $sqlSuppliers);
            extract($editingDelete);
        } else {
            $editingElse = $this->editingElse();
            extract($editingElse);
        }

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => $errors,
            'editingSupplier' => $supplier,
            'editing' => $editing,
            'suppliers' => $listSuppliers,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'token' => $token,
        ], 'index');
    }

    private function editingSelect($postSupplier, $listSuppliers)
    {
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $editing = 'show';
        $errors['editing'] = [];

        if (strpos($postSupplier['supplier_name'], '@')) {
            $supplier['supplier_id'] = strstr($postSupplier['supplier_name'], '@', true);
            $supplier['supplier_name'] = substr(strstr($postSupplier['supplier_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->supplierValidate($supplier, $listSuppliers, 'select');
        } else {
            $errors['editing']['supplier_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $_SESSION['supplier_id'] = $supplier['supplier_id'];
            $_SESSION['supplier_name'] = $supplier['supplier_name'];
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        } else {
            $supplier = [];
        }

        return [
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editing' => $editing,
            'errors' => $errors,
            'supplier' => $supplier,
        ];
    }

    private function editingUpdate($postSupplier, $sqlSuppliers)
    {
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $editing = 'show';
        $errors['editing'] = [];

        $supplier['supplier_name'] = $postSupplier['supplier_name'];

        if (isset($_SESSION['supplier_id'])) {
            $supplier['supplier_id'] = $_SESSION['supplier_id'];
        } else {
            throw new HttpNotFoundException();
        }
        $errors['editing'] = $errors['editing'] + $this->validate->supplierValidate($supplier);
        if (!count($errors['editing'])) {
            $sqlSuppliers->update($supplier);
            $supplier = [];
            unset($_SESSION['supplier_name']);
            unset($_SESSION['supplier_id']);
            $listSuppliers = $sqlSuppliers->fetchAllSupplier();
            $this->convert->convertJson($listSuppliers, 'supplier');
        } else {
            $supplier['name'] = $_SESSION['supplier_name'];
            $editing = 'show';
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        }

        return [
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editing' => $editing,
            'errors' => $errors,
            'supplier' => $supplier,
            'listSuppliers' => $listSuppliers,
        ];
    }

    private function editingDelete($postSupplier, $listSuppliers, $sqlSuppliers)
    {
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $editing = 'show';
        $errors['editing'] = [];

        if (strpos($postSupplier['supplier_name'], '@')) {
            $supplier['supplier_id'] = strstr($postSupplier['supplier_name'], '@', true);
            $supplier['name'] = substr(strstr($postSupplier['supplier_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->supplierValidate($supplier, $listSuppliers, 'delete');

            $sqlProduct = $this->databaseManager->get('Product');
            $busySupplier = $sqlProduct->searchProducts($supplier['supplier_id']);
            $boolBusySupplier = !is_null($busySupplier);
            if ($boolBusySupplier) {
                $errors['editing']['supplier_name'] = '関連する商品があるため削除できません';
            }
        } else {
            $errors['editing']['supplier_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $supplier['supplier_name'] = $supplier['name'];
            $sqlSuppliers->delete($supplier);
            $listSuppliers = $sqlSuppliers->fetchAllSupplier();
            $this->convert->convertJson($listSuppliers, 'supplier');
            $supplier = [];
        } else {
            $editing = 'show';
            $editingFieldset = 'disabled';
            $selectFieldset = '';
            $supplier = [];
        }

        return [
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editing' => $editing,
            'errors' => $errors,
            'supplier' => $supplier,
            'listSuppliers' => $listSuppliers,
        ];
    }

    private function editingElse()
    {
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $editing = 'show';
        $errors['editing'] = [];

        $supplier = [];
        if (isset($_SESSION['supplier_id'])) {
            unset($_SESSION['supplier_id']);
        }
        if (isset($_SESSION['supplier_name'])) {
            unset($_SESSION['supplier_name']);
        }

        return [
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editing' => $editing,
            'errors' => $errors,
            'supplier' => $supplier,
        ];
    }
}
