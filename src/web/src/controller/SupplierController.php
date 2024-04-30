<?php

$errors = [];

class SupplierController extends Controller
{
    public function index()
    {
        $navbar = $this->navbar;
        $role = "not";

        session_start();
        $token = $this->createToken();

        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }
        if (!in_array("purchases", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

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
        if (!$this->request->isPost()) {
            throw new HttpNotFoundException();
        }

        $errors['increase'] = [];

        $navbar = $this->navbar;
        $role = "not";

        session_start();
        $token = $this->judgeToken();

        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }
        if (!in_array("purchases", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

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

        if (isset($_POST['supplier_id'])) {
            $supplier['supplier_id'] = trim($_POST['supplier_name']);
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
        if (!$this->request->isPost()) {
            throw new HttpNotFoundException();
        }

        $errors['editing'] = [];

        $navbar = $this->navbar;
        $role = "not";

        session_start();
        $token = $this->judgeToken();

        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }
        if (!in_array("purchases", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリのSupplierクラスをnewして$sqlSuppliersに渡す
        $sqlSuppliers = $this->databaseManager->get('Supplier');

        if (isset($_POST['supplier_name'])) {
            $supplier['supplier_name'] = trim($_POST['supplier_name']);
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
            $editing = 'show';
            if (strpos($supplier['supplier_name'], '@')) {
                $supplier['supplier_id'] = strstr($supplier['supplier_name'], '@', true);
                $supplier['name'] = substr(strstr($supplier['supplier_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->supplierValidate($supplier, $listSuppliers, 'select');
            } else {
                $errors['editing']['supplier_name'] = '選択肢から選んでください';
            }

            if (!count($errors['editing'])) {
                $supplier['supplier_name'] = $supplier['name'];
                $_SESSION['supplier_id'] = $supplier['supplier_id'];
                $_SESSION['supplier_name'] = $supplier['name'];
                $editingFieldset = '';
                $selectFieldset = 'disabled';
            } else {
                $supplier = [];
            }
        } elseif (isset($_POST['update'])) {
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
        } elseif (isset($_POST['delete'])) {
            if (strpos($supplier['supplier_name'], '@')) {
                $supplier['supplier_id'] = strstr($supplier['supplier_name'], '@', true);
                $supplier['name'] = substr(strstr($supplier['supplier_name'], '@', false), 1);
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
        } else {
            $supplier = [];
            if (isset($_SESSION['supplier_id'])) {
                unset($_SESSION['supplier_id']);
            }
            if (isset($_SESSION['supplier_name'])) {
                unset($_SESSION['supplier_name']);
            }
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
}
