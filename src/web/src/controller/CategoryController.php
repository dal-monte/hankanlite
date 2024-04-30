<?php

$errors = [];

class CategoryController extends Controller
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

        if (isset($_SESSION['category_id'])) {
            unset($_SESSION['category_id']);
        }
        if (isset($_SESSION['category_name'])) {
            unset($_SESSION['category_name']);
        }

        $sqlCategories = $this->databaseManager->get('Category');
        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();
        $this->convert->convertJson($listCategories, 'category');

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => [],
            'categories' => $listCategories,
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

        if (isset($_SESSION['category_id'])) {
            unset($_SESSION['category_id']);
        }
        if (isset($_SESSION['category_name'])) {
            unset($_SESSION['category_name']);
        }

        $increase = 'show';

        // modelsディレクトリのCategoryクラスをnewして$sqlCategoriesに渡す
        $sqlCategories = $this->databaseManager->get('Category');

        if (isset($_POST['category_name'])) {
            $category['category_name'] = trim($_POST['category_name']);
        } else {
            throw new HttpNotFoundException();
        }

        $listCategories = $sqlCategories->fetchAllCategory();
        $errors['increase'] = $errors['increase'] + $this->validate->categoryValidate($category, $listCategories, 'increase');

        if (!count($errors['increase'])) {
            $sqlCategories->insert($category['category_name']);
            $category = [];
            $increase = '';
        }

        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();
        $this->convert->convertJson($listCategories, 'category');

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => $errors,
            'categories' => $listCategories,
            'category' => $category,
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

        // modelsディレクトリのCategoryクラスをnewして$sqlCategoriesに渡す
        $sqlCategories = $this->databaseManager->get('Category');

        if (isset($_POST['category_name'])) {
            $category['category_name'] = trim($_POST['category_name']);
        } else {
            throw new HttpNotFoundException();
        }

        $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($category);

        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();
        $this->convert->convertJson($listCategories, 'category');

        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        if (isset($_POST['select'])) {
            $editing = 'show';
            if (strpos($category['category_name'], '@')) {
                $category['category_id'] = strstr($category['category_name'], '@', true);
                $category['name'] = substr(strstr($category['category_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($category, $listCategories, 'select');
            } else {
                $errors['editing']['category_name'] = '選択肢から選んでください';
            }

            if (!count($errors['editing'])) {
                $category['category_name'] = $category['name'];
                $_SESSION['category_id'] = $category['category_id'];
                $_SESSION['category_name'] = $category['category_name'];
                $editingFieldset = '';
                $selectFieldset = 'disabled';
            }

            if (count($errors['editing'])) {
                $category = [];
            }
        } elseif (isset($_POST['update'])) {
            if (isset($_SESSION['category_id'])) {
                $category['category_id'] = $_SESSION['category_id'];
            } else {
                throw new HttpNotFoundException();
            }
            $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($category, $listCategories, 'update');
            if (!count($errors['editing'])) {
                $sqlCategories->update($category);
                $category = [];
                unset($_SESSION['category_name']);
                unset($_SESSION['category_id']);
                $listCategories = $sqlCategories->fetchAllCategory();
                $this->convert->convertJson($listCategories, 'category');
            } else {
                $category['name'] = $_SESSION['category_name'];
                $editing = 'show';
                $editingFieldset = '';
                $selectFieldset = 'disabled';
            }
        } elseif (isset($_POST['delete'])) {
            if (strpos($category['category_name'], '@')) {
                $category['category_id'] = strstr($category['category_name'], '@', true);
                $category['name'] = substr(strstr($category['category_name'], '@', false), 1);
                $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($category, $listCategories, 'delete');

                $sqlProduct = $this->databaseManager->get('Product');
                $busyCategory = $sqlProduct->searchProducts($category['category_id']);
                $boolBusyCategory = !is_null($busyCategory);
                if ($boolBusyCategory) {
                    $errors['editing']['category_name'] = '関連する商品があるため削除できません';
                }
            } else {
                $errors['editing']['category_name'] = '選択肢から選んでください';
            }

            if (!count($errors['editing'])) {
                $category['category_name'] = $category['name'];
                $sqlCategories->delete($category);
                $listCategories = $sqlCategories->fetchAllCategory();
                $this->convert->convertJson($listCategories, 'category');
                $category = [];
            } else {
                $editing = 'show';
                $editingFieldset = 'disabled';
                $selectFieldset = '';
            }
        } else {
            $category = [];
            if (isset($_SESSION['category_id'])) {
                unset($_SESSION['category_id']);
            }
            if (isset($_SESSION['category_name'])) {
                unset($_SESSION['category_name']);
            }
        }

        return $this->render([
            'title' => 'カテゴリー',
            'errors' => $errors,
            'editingCategory' => $category,
            'editing' => $editing,
            'categories' => $listCategories,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'token' => $token,
        ], 'index');
    }
}
