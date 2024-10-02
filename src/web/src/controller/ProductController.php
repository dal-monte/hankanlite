<?php

class ProductController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("products");

        if (isset($_SESSION['product_id'])) {
            unset($_SESSION['product_id']);
        }
        if (isset($_SESSION['product_name'])) {
            unset($_SESSION['product_name']);
        }

        $sqlCategories  = $this->databaseManager->get('Category');
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts  = $this->databaseManager->get('Product');
        $listProducts = $sqlProducts->fetchAllProduct();
        $this->convert->convertJson($listProducts, 'product');

        $usedCategories = array_column($listProducts, 'category_id');

        $listUsedCategories = null;
        foreach ($listCategories as $listCategory) {
            if (in_array($listCategory['category_id'], $usedCategories, true)) {
                $listUsedCategories[] = $listCategory;
            }
        }
        if (is_null($listUsedCategories)) {
            $listUsedCategories = [];
        }

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => [],
            'categories' => $listCategories,
            'products' => $listProducts,
            'usedCategories' => $listUsedCategories,
            'token' => $token,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("products");

        $errors['increase'] = [];

        if (isset($_SESSION['product_id'])) {
            unset($_SESSION['product_id']);
        }
        if (isset($_SESSION['product_name'])) {
            unset($_SESSION['product_name']);
        }

        $sqlCategories  = $this->databaseManager->get('Category');
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts  = $this->databaseManager->get('Product');
        $listProducts = $sqlProducts->fetchAllProduct();
        $this->convert->convertJson($listProducts, 'product');

        $sqlStock = $this->databaseManager->get('Stock');

        $usedCategories = array_column($listProducts, 'category_id');
        $listUsedCategories = null;
        foreach ($listCategories as $listCategory) {
            if (in_array($listCategory['category_id'], $usedCategories, true)) {
                $listUsedCategories[] = $listCategory;
            }
        }
        if (is_null($listUsedCategories)) {
            $listUsedCategories = [];
        }

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
            $product['category_id']   = strstr($product['category_name'], '@', true);
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

            $usedCategories = array_column($listProducts, 'category_id');
            $listUsedCategories = null;
            foreach ($listCategories as $listCategory) {
                if (in_array($listCategory['category_id'], $usedCategories, true)) {
                    $listUsedCategories[] = $listCategory;
                }
            }
            if (is_null($listUsedCategories)) {
                $listUsedCategories = [];
            }
        }

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'increase' => $increase,
            'increaseProduct' => $product,
            'categories' => $listCategories,
            'products' => $listProducts,
            'usedCategories' => $listUsedCategories,
            'token' => $token,
        ], 'index');
    }

    public function editing()
    {
        session_start();

        $token = $this->securityCheck("products");

        $sqlCategories  = $this->databaseManager->get('Category');
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts    = $this->databaseManager->get('Product');
        $listProducts   = $sqlProducts->fetchAllProduct();
        $this->convert->convertJson($listProducts, 'product');

        $usedCategories = array_column($listProducts, 'category_id');
        $listUsedCategories = null;
        foreach ($listCategories as $listCategory) {
            if (in_array($listCategory['category_id'], $usedCategories, true)) {
                $listUsedCategories[] = $listCategory;
            }
        }
        if (is_null($listUsedCategories)) {
            $listUsedCategories = [];
        }

        if (isset($_POST['search'])) {
            $editingSearch = $this->editingSearch($listProducts, $listCategories);
            extract($editingSearch);
        } elseif (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($listProducts);
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate($listProducts, $sqlProducts, $listCategories);
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($listProducts, $sqlProducts);
            extract($editingDelete);
        } else {
            $editingElse   = $this->editingElse();
            extract($editingElse);
        }

        $usedCategories = array_column($listProducts, 'category_id');
        $listUsedCategories = null;
        foreach ($listCategories as $listCategory) {
            if (in_array($listCategory['category_id'], $usedCategories, true)) {
                $listUsedCategories[] = $listCategory;
            }
        }
        if (is_null($listUsedCategories)) {
            $listUsedCategories = [];
        }

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'editingProduct' => $product,
            'editing' => $editing,
            'searchFieldset' => $searchFieldset,
            'selectFieldset' => $selectFieldset,
            'editingFieldset' => $editingFieldset,
            'categories' => $listCategories,
            'products' => $listProducts,
            'usedCategories' => $listUsedCategories,
            'selector' => $selector,
            'token' => $token,
        ], 'index');
    }

    private function editingSearch($listProducts, $listCategories)
    {
        $errors['editing'] = [];
        $editing = 'show';
        $searchFieldset = '';
        $selectFieldset = '';
        $editingFieldset = 'disabled';
        $selector = 'show';

        if (isset($_POST['category_name'])) {
            $postCategory['category_name'] = trim($_POST['category_name']);
        } else {
            throw new HttpNotFoundException();
        }

        if (strpos($postCategory['category_name'], '@')) {
            $category['category_id'] = strstr($postCategory['category_name'], '@', true);
            $category['category_name'] = substr(strstr($postCategory['category_name'], '@', false), 1);
            $errors['editing'] = $this->validate->categoryValidate($category, $listCategories, 'select', $listProducts);
        } else {
            $errors['editing']['category_name'] = 'カテゴリーを選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            foreach ($listProducts as $listProduct) {
                if ($category['category_id'] === $listProduct['category_id']) {
                    $checkedProducts[] = $listProduct;
                };
            }
            $listProducts = $checkedProducts;
            $product = $category;
            $searchFieldset = 'disabled';
        }

        return [
            'editing' => $editing,
            'product' => $product,
            'errors' => $errors,
            'listProducts' => $listProducts,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'selector' => $selector,
            'searchFieldset' => $searchFieldset,
        ];
    }

    private function editingSelect($listProducts)
    {
        $errors['editing'] = [];
        $editing = 'show';
        $searchFieldset = '';
        $selectFieldset = '';
        $editingFieldset = 'disabled';
        $selector = '';

        if (isset($_POST['product_name'])) {
            $postProduct['product_name'] = trim($_POST['product_name']);
        } else {
            throw new HttpNotFoundException();
        }

        if (strpos($postProduct['product_name'], '@')) {
            $product['product_id'] = strstr($postProduct['product_name'], '@', true);
            $product['product_name']       = substr(strstr($postProduct['product_name'], '@', false), 1);
            $errors['editing']     = $errors['editing'] + $this->validate->productValidate($product, $listProducts, 'select');
        } else {
            $errors['editing']['product_name'] = '商品を選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $_SESSION['product_id']   = $product['product_id'];
            $_SESSION['product_name'] = $product['product_name'];
            foreach ($listProducts as $productData) {
                if ($productData['product_id'] === $product['product_id']) {
                    $product = $productData;
                }
            }
            $searchFieldset = 'disabled';
            $selectFieldset = 'disabled';
            $editingFieldset = '';
            $selector = 'disabled';
        } else {
            $product = [];
        }
        return [
            'editing' => $editing,
            'product' => $product,
            'errors' => $errors,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'selector' => $selector,
            'searchFieldset' => $searchFieldset,
        ];
    }

    private function editingUpdate($listProducts, $sqlProducts, $listCategories)
    {
        $errors['editing'] = [];
        $editing = 'show';
        $searchFieldset = '';
        $selectFieldset = '';
        $editingFieldset = 'disabled';
        $selector = '';

        if (isset($_POST['product_name'])) {
            $postProduct['product_name'] = trim($_POST['product_name']);
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['category_name'])) {
            $postProduct['category_name'] = $_POST['category_name'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['editing_price'])) {
            $postProduct['list_price'] = trim(str_replace(',', '', $_POST['editing_price']));
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION['product_id'])) {
            $product['product_id'] = $_SESSION['product_id'];
        } else {
            throw new HttpNotFoundException();
        }

        if (strpos($postProduct['category_name'], '@')) {
            $product['category_id']   = strstr($postProduct['category_name'], '@', true);
            $product['category_name'] = substr(strstr($postProduct['category_name'], '@', false), 1);
            $product['product_name'] = $postProduct['product_name'];
            $product['list_price'] = $postProduct['list_price'];
            $errors['editing']        = $errors['editing'] + $this->validate->categoryValidate($product, $listCategories, 'select');
        } else {
            $errors['editing']['category_name'] = 'カテゴリーを選択肢から選んでください';
        }

        $errors['editing'] = $errors['editing'] + $this->validate->productValidate($product, $listProducts, 'update');

        if (!count($errors['editing'])) {
            $sqlProducts->update($product);
            $product = [];
            unset($_SESSION['product_name']);
            unset($_SESSION['product_id']);
            $listProducts = $sqlProducts->fetchAllProduct();
            $this->convert->convertJson($listProducts, 'product');
            $editing = '';
        } else {
            $product['product_name'] = $_SESSION['product_name'];
            $selectFieldset = 'disabled';
            $editingFieldset = '';
            $selector = 'disabled';
        }

        return [
            'editing' => $editing,
            'product' => $product,
            'errors' => $errors,
            'searchFieldset' => $searchFieldset,
            'selectFieldset' => $selectFieldset,
            'editingFieldset' => $editingFieldset,
            'selector' => $selector,
        ];
    }

    private function editingDelete($listProducts, $sqlProducts)
    {
        $errors['editing'] = [];
        $editing = 'show';
        $searchFieldset = '';
        $selectFieldset = '';
        $editingFieldset = 'disabled';
        $selector = '';

        if (isset($_POST['product_name'])) {
            $postProduct['product_name'] = trim($_POST['product_name']);
        } else {
            throw new HttpNotFoundException();
        }

        if (strpos($postProduct['product_name'], '@')) {
            $product['product_id']   = strstr($postProduct['product_name'], '@', true);
            $product['product_name'] = substr(strstr($postProduct['product_name'], '@', false), 1);
            $errors['editing']     = $errors['editing'] + $this->validate->productValidate($product, $listProducts, 'delete');

            $sqlPurchaseProduct = $this->databaseManager->get('PurchaseProduct');
            $sqlSalesProduct    = $this->databaseManager->get('SalesProduct');

            $busyPurchaseProduct = $sqlPurchaseProduct->searchContract($product['product_id']);
            $busySalesProduct    = $sqlSalesProduct->searchContract($product['product_id']);
            $boolBusyProduct     = !empty($busyPurchaseProduct) or !empty($busySalesProduct);
            if ($boolBusyProduct) {
                $errors['editing']['product_name'] = '関連する契約があるため削除できません';
            }
        } else {
            $errors['editing']['product_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $sqlProducts->delete($product);
            $listProducts = $sqlProducts->fetchAllProduct();
            $this->convert->convertJson($listProducts, 'product');
            $product = [];
            $editing = '';
        }
        return [
            'editing' => $editing,
            'product' => $product,
            'errors' => $errors,
            'searchFieldset' => $searchFieldset,
            'selectFieldset' => $selectFieldset,
            'editingFieldset' => $editingFieldset,
            'selector' => $selector,
        ];
    }

    private function editingElse()
    {
        $errors['editing'] = [];
        $editing = 'show';
        $searchFieldset = '';
        $selectFieldset = '';
        $editingFieldset = 'disabled';
        $selector = '';

        $product = [];
        $editing = '';
        if (isset($_SESSION['product_id'])) {
            unset($_SESSION['product_id']);
        }
        if (isset($_SESSION['product_name'])) {
            unset($_SESSION['product_name']);
        }

        return [
            'editing' => $editing,
            'product' => $product,
            'errors' => $errors,
            'searchFieldset' => $searchFieldset,
            'selectFieldset' => $selectFieldset,
            'editingFieldset' => $editingFieldset,
            'selector' => $selector,
        ];
    }
}
