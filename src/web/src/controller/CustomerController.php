<?php

$errors = [];

class CustomerController extends Controller
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
        if (!in_array("sales", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

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
        if (!$this->request->isPost()) {
            throw new HttpNotFoundException();
        }

        session_start();
        $token = $this->judgeToken();

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
        if (!in_array("products", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリのCustomerクラスをnewして$sqlCustomersに渡す
        $sqlCustomers = $this->databaseManager->get('Customer');

        if (isset($_POST['customer_name'])) {
            $customer['customer_name'] = trim($_POST['customer_name']);
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
            $editing = 'show';
            if (strpos($customer['customer_name'], '@')) {
                $customer['customer_id'] = strstr($customer['customer_name'], '@', true);
                $customer['name'] = substr(strstr($customer['customer_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->customerValidate($customer, $sqlCustomers, 'select');
            } else {
                $errors['editing']['customer_name'] = '選択肢から選んでください';
            }

            if (!count($errors['editing'])) {
                $customer['customer_name'] = $customer['name'];
                $_SESSION['customer_id'] = $customer['customer_id'];
                $_SESSION['customer_name'] = $customer['customer_name'];
                $editingFieldset = '';
                $selectFieldset = 'disabled';
            } else {
                $customer = [];
            }
        } elseif (isset($_POST['update'])) {
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
                $customer['name'] = $_SESSION['customer_name'];
                $editing = 'show';
                $editingFieldset = '';
                $selectFieldset = 'disabled';
            }
        } elseif (isset($_POST['delete'])) {
            if (strpos($customer['customer_name'], '@')) {
                $customer['customer_id'] = strstr($customer['customer_name'], '@', true);
                $customer['name'] = substr(strstr($customer['customer_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->customerValidate($customer, $sqlCustomers, 'delete');

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
                $customer['customer_name'] = $customer['name'];
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
        } else {
            $customer = [];
            if (isset($_SESSION['customer_id'])) {
                unset($_SESSION['customer_id']);
            }
            if (isset($_SESSION['customer_name'])) {
                unset($_SESSION['customer_name']);
            }
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
}
