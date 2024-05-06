<?php

class CustomerController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("sales");

        if (isset($_SESSION['customer_id'])) {
            unset($_SESSION['customer_id']);
        }
        if (isset($_SESSION['customer_name'])) {
            unset($_SESSION['customer_name']);
        }

        $sqlCustomers = $this->databaseManager->get('Customer');
        // // mysqlのcustomersテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCustomers = $sqlCustomers->fetchAllCustomer();
        $this->convert->convertJson($listCustomers, 'customer');

        return $this->render([
            'title' => '顧客の登録',
            'errors' => [],
            'customers' => $listCustomers,
            'token' => $token,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("sales");

        if (isset($_SESSION['customer_id'])) {
            unset($_SESSION['customer_id']);
        }
        if (isset($_SESSION['customer_name'])) {
            unset($_SESSION['customer_name']);
        }

        $errors['increase'] = [];

        $navbar = $this->navbar;
        $role = "not";
        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }
        if (!in_array("products", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        $increase = 'show';

        // modelsディレクトリのCustomerクラスをnewして$sqlCustomersに渡す
        $sqlCustomers = $this->databaseManager->get('Customer');

        if (isset($_POST['customer_id']) && isset($_POST['customer_name'])) {
            $customer = [
                'customer_id' => trim($_POST['customer_id']),
                'customer_name' => trim($_POST['customer_name']),
            ];
        } else {
            throw new HttpNotFoundException();
        }

        $listCustomers = $sqlCustomers->fetchAllCustomer();
        $errors['increase'] = $errors['increase'] + $this->validate->customerValidate($customer, $sqlCustomers, 'increase');

        if (!count($errors['increase'])) {
            $sqlCustomers->insert($customer);
            $customer = [];
            $increase = '';
            $listCustomers = $sqlCustomers->fetchAllCustomer();
            $this->convert->convertJson($listCustomers, 'customer');
        }

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => $errors,
            'customers' => $listCustomers,
            'customer' => $customer,
            'increase' => $increase,
            'token' => $token,
        ], 'index');
    }

    public function editing()
    {
        session_start();

        $token = $this->securityCheck("sales");

        // modelsディレクトリのCustomerクラスをnewして$sqlCustomersに渡す
        $sqlCustomers = $this->databaseManager->get('Customer');

        if (isset($_POST['customer_name'])) {
            $postCustomer['customer_name'] = trim($_POST['customer_name']);
        } else {
            throw new HttpNotFoundException();
        }

        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCustomers = $sqlCustomers->fetchAllCustomer();
        $this->convert->convertJson($listCustomers, 'customer');

        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        if (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($postCustomer, $listCustomers);
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate($postCustomer, $listCustomers, $sqlCustomers);
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($postCustomer, $listCustomers, $sqlCustomers);
            extract($editingDelete);
        } else {
            $editingElse   = $this->editingElse();
            extract($editingElse);
        }

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => $errors,
            'editingCustomer' => $customer,
            'editing' => $editing,
            'customers' => $listCustomers,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'token' => $token,
        ], 'index');
    }

    private function editingSelect($postCustomer, $listCustomers)
    {
        $errors['editing'] = [];
        $editing = 'show';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        if (strpos($postCustomer['customer_name'], '@')) {
            $customer['customer_id'] = strstr($postCustomer['customer_name'], '@', true);
            $customer['customer_name'] = substr(strstr($postCustomer['customer_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->customerValidate($customer, $listCustomers, 'select');
        } else {
            $errors['editing']['customer_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $_SESSION['customer_id'] = $customer['customer_id'];
            $_SESSION['customer_name'] = $customer['customer_name'];
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        } else {
            $customer = [];
        }
        return [
            'editing' => $editing,
            'customer' => $customer,
            'errors' => $errors,
            'listCustomers' => $listCustomers,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }

    private function editingUpdate($postCustomer, $listCustomers, $sqlCustomers)
    {
        $errors['editing'] = [];
        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        $customer['customer_name'] = $postCustomer['customer_name'];

        if (isset($_SESSION['customer_id'])) {
            $customer['customer_id'] = $_SESSION['customer_id'];
        } else {
            throw new HttpNotFoundException();
        }
        $errors['editing'] = $errors['editing'] + $this->validate->customerValidate($customer);
        if (!count($errors['editing'])) {
            $sqlCustomers->update($customer);
            $customer = [];
            unset($_SESSION['customer_name']);
            unset($_SESSION['customer_id']);
            $listCustomers = $sqlCustomers->fetchAllCustomer();
            $this->convert->convertJson($listCustomers, 'customer');
        } else {
            $customer['category_name'] = $_SESSION['customer_name'];
            $editing = 'show';
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        }

        return [
            'editing' => $editing,
            'customer' => $customer,
            'errors' => $errors,
            'listCustomers' => $listCustomers,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }

    private function editingDelete($postCustomer, $listCustomers, $sqlCustomers)
    {
        $errors['editing'] = [];
        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        if (strpos($postCustomer['customer_name'], '@')) {
            $customer['customer_id'] = strstr($postCustomer['customer_name'], '@', true);
            $customer['customer_name'] = substr(strstr($postCustomer['customer_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->customerValidate($customer, $listCustomers, 'delete');

            $sqlProduct = $this->databaseManager->get('Product');
            $busyCustomer = $sqlProduct->searchProducts($customer['customer_id']);
            $boolBusyCustomer = !is_null($busyCustomer);
            if ($boolBusyCustomer) {
                $errors['editing']['customer_name'] = '関連する商品があるため削除できません';
            }
        } else {
            $errors['editing']['customer_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $sqlCustomers->delete($customer);
            $listCustomers = $sqlCustomers->fetchAllCustomer();
            $this->convert->convertJson($listCustomers, 'purchaseProduct');
            $customer = [];
        } else {
            $editing = 'show';
            $editingFieldset = 'disabled';
            $selectFieldset = '';
            $customer = [];
        }

        return [
            'editing' => $editing,
            'customer' => $customer,
            'errors' => $errors,
            'listCustomers' => $listCustomers,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }

    private function editingElse()
    {
        $errors['editing'] = [];
        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        $customer = [];
        if (isset($_SESSION['customer_id'])) {
            unset($_SESSION['customer_id']);
        }
        if (isset($_SESSION['customer_name'])) {
            unset($_SESSION['customer_name']);
        }

        return [
            'editing' => $editing,
            'customer' => $customer,
            'errors' => $errors,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }
}
