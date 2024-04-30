<?php

$errors = [];

class ProductController extends Controller
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
        if (!in_array("products", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION['product_id'])) {
            unset($_SESSION['product_id']);
        }
        if (isset($_SESSION['product_name'])) {
            unset($_SESSION['product_name']);
        }

        $sqlCategories = $this->databaseManager->get('Category');
        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts = $this->databaseManager->get('Product');
        $listProducts = $sqlProducts->fetchAllProduct();

        $this->convert->convertJson($listProducts, 'product');

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => [],
            'categories' => $listCategories,
            'products' => $listProducts,
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
        if (!in_array("products", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION['product_id'])) {
            unset($_SESSION['product_id']);
        }
        if (isset($_SESSION['product_name'])) {
            unset($_SESSION['product_name']);
        }

        $sqlCategories = $this->databaseManager->get('Category');
        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts = $this->databaseManager->get('Product');
        $listProducts = $sqlProducts->fetchAllProduct();

        $sqlStock = $this->databaseManager->get('Stock');

        $this->convert->convertJson($listProducts, 'product');

        $increase = 'show';

        if (isset($_POST['category_name'])) {
            $product['category_name'] = $_POST['category_name'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['product_name'])) {
            $product['product_name'] = trim($_POST['product_name']);
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['increase_price'])) {
            $product['list_price'] = trim(str_replace(',', '', $_POST['increase_price']));
        } else {
            throw new HttpNotFoundException();
        }

        $errors['increase'] = $errors['increase'] + $this->validate->productValidate($product, $listProducts, 'increase');

        if (strpos($product['category_name'], '@')) {
            $product['category_id'] = strstr($product['category_name'], '@', true);
            $product['category_name'] = substr(strstr($product['category_name'], '@', false), 1);
            $errors['increase'] = $errors['increase'] + $this->validate->categoryValidate($product, $listCategories, 'select');
        } else {
            $errors['increase']['category_name'] = 'カテゴリーを選択肢から選んでください';
        }

        if (!count($errors['increase'])) {
            $newProductId = $sqlProducts->insert($product);
            $sqlStock->insert($newProductId);
            $product = [];
            $listProducts = $sqlProducts->fetchAllProduct();
            $this->convert->convertJson($listProducts, 'product');
            $increase = '';
        }

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'increase' => $increase,
            'increaseProduct' => $product,
            'categories' => $listCategories,
            'products' => $listProducts,
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

        $sqlCategories = $this->databaseManager->get('Category');
        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts = $this->databaseManager->get('Product');
        $listProducts = $sqlProducts->fetchAllProduct();

        $sqlStock = $this->databaseManager->get('Stock');

        $this->convert->convertJson($listProducts, 'product');

        if (isset($_POST['product_name'])) {
            $product['product_name'] = trim($_POST['product_name']);
        } else {
            throw new HttpNotFoundException();
        }

        $editing = 'show';
        $selectFieldset = '';
        $editingFieldset = 'disabled';
        $selector = '';

        if (isset($_POST['select'])) {
            if (strpos($product['product_name'], '@')) {
                $product['product_id'] = strstr($product['product_name'], '@', true);
                $product['name'] = substr(strstr($product['product_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->productValidate($product, $listProducts, 'select');
            } else {
                $errors['editing']['product_name'] = '商品を選択肢から選んでください';
            }

            if (!count($errors['editing'])) {
                $_SESSION['product_id'] = $product['product_id'];
                $_SESSION['product_name'] = $product['name'];
                foreach ($listProducts as $productData) {
                    if ($productData['product_id'] === $product['product_id']) {
                        $product = $productData;
                        $product['product_name'] = $productData['name'];
                    }
                }
                $selectFieldset = 'disabled';
                $editingFieldset = '';
                $selector = 'disabled';
            }
        } elseif (isset($_POST['update'])) {
            if (isset($_SESSION['product_id'])) {
                $product['product_id'] = $_SESSION['product_id'];
            } else {
                throw new HttpNotFoundException();
            }

            if (isset($_POST['category_name'])) {
                $product['category_name'] = $_POST['category_name'];
            } else {
                throw new HttpNotFoundException();
            }

            if (strpos($product['category_name'], '@')) {
                $product['category_id'] = strstr($product['category_name'], '@', true);
                $product['category_name'] = substr(strstr($product['category_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($product, $listCategories, 'select');
            } else {
                $errors['editing']['category_name'] = 'カテゴリーを選択肢から選んでください';
            }

            if (isset($_POST['product_name'])) {
                $product['name'] = trim($_POST['product_name']);
            } else {
                throw new HttpNotFoundException();
            }

            if (isset($_POST['editing_price'])) {
                $product['list_price'] = trim(str_replace(',', '', $_POST['editing_price']));
            } else {
                throw new HttpNotFoundException();
            }

            $errors['editing'] = $errors['editing'] + $this->validate->productValidate($product, $listProducts, 'update');

            if (!count($errors['editing'])) {
                $sqlProducts->update($product);
                $product = [];
                unset($_SESSION['product_name']);
                unset($_SESSION['product_id']);
                $listProducts = $sqlProducts->fetchAllProduct();
                $this->convert->convertJson($listProducts, 'product');
            } else {
                $product['name'] = $_SESSION['product_name'];
                $selectFieldset = 'disabled';
                $editingFieldset = '';
                $selector = 'disabled';
            }
        } elseif (isset($_POST['delete'])) {
            if (strpos($product['product_name'], '@')) {
                $product['product_id'] = strstr($product['product_name'], '@', true);
                $product['name'] = substr(strstr($product['product_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->productValidate($product, $listProducts, 'delete');

                $sqlPurchaseProduct = $this->databaseManager->get('PurchaseProduct');
                $sqlSalesProduct = $this->databaseManager->get('SalesProduct');

                $busyPurchaseProduct = $sqlPurchaseProduct->searchContract($product['product_id']);
                $busySalesProduct = $sqlSalesProduct->searchContract($product['product_id']);
                $boolBusyProduct = !empty($busyPurchaseProduct) or !empty($busySalesProduct);
                if ($boolBusyProduct) {
                    $errors['editing']['product_name'] = '関連する契約があるため削除できません';
                }
            } else {
                $errors['editing']['product_name'] = '選択肢から選んでください';
            }

            if (!count($errors['editing'])) {
                $product['product_name'] = $product['name'];
                $sqlProducts->delete($product);
                $listProducts = $sqlProducts->fetchAllProduct();
                $this->convert->convertJson($listProducts, 'product');
                $product = [];
                $editing = '';
            }
        } else {
            $product = [];
            $editing = '';
            if (isset($_SESSION['product_id'])) {
                unset($_SESSION['product_id']);
            }
            if (isset($_SESSION['product_name'])) {
                unset($_SESSION['product_name']);
            }
        }

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'editingProduct' => $product,
            'editing' => $editing,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'categories' => $listCategories,
            'products' => $listProducts,
            'selector' => $selector,
            'token' => $token,
        ], 'index');
    }
}
