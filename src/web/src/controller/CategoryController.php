<?php

class CategoryController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("products");

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
        session_start();

        $token = $this->securityCheck("products");

        $errors['increase'] = [];

        if (isset($_SESSION['category_id'])) {
            unset($_SESSION['category_id']);
        }
        if (isset($_SESSION['category_name'])) {
            unset($_SESSION['category_name']);
        }

        $increase = 'show';

        if (isset($_POST['category_name'])) {
            $category['category_name'] = trim($_POST['category_name']);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリのCategoryクラスをnewして$sqlCategoriesに渡す
        $sqlCategories = $this->databaseManager->get('Category');

        // dbのカテゴリーをfetchAllする
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
        session_start();

        $token = $this->securityCheck("products");

        // modelsディレクトリのCategoryクラスをnewして$sqlCategoriesに渡す
        $sqlCategories = $this->databaseManager->get('Category');

        if (isset($_POST['category_name'])) {
            $postCategory['category_name'] = trim($_POST['category_name']);
        } else {
            throw new HttpNotFoundException();
        }

        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listCategories = $sqlCategories->fetchAllCategory();
        $this->convert->convertJson($listCategories, 'category');

        if (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($postCategory, $listCategories);
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate($postCategory, $listCategories, $sqlCategories);
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($postCategory, $listCategories, $sqlCategories);
            extract($editingDelete);
        } else {
            $editingElse   = $this->editingElse();
            extract($editingElse);
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

    private function editingSelect($postCategory, $listCategories)
    {
        $errors['editing'] = [];
        $editing = 'show';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        if (strpos($postCategory['category_name'], '@')) {
            $category['category_id'] = strstr($postCategory['category_name'], '@', true);
            $category['category_name'] = substr(strstr($postCategory['category_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($category, $listCategories, 'select');
        } else {
            $errors['editing']['category_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $_SESSION['category_id'] = $category['category_id'];
            $_SESSION['category_name'] = $category['category_name'];
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        } else {
            $category = [];
        }
        return [
            'editing' => $editing,
            'category' => $category,
            'errors' => $errors,
            'listCategories' => $listCategories,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }

    private function editingUpdate($postCategory, $listCategories, $sqlCategories)
    {
        $errors['editing'] = [];
        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        $category['category_name'] = $postCategory['category_name'];

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
            $category['category_name'] = $_SESSION['category_name'];
            $editing = 'show';
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        }

        return [
            'editing' => $editing,
            'category' => $category,
            'errors' => $errors,
            'listCategories' => $listCategories,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }

    private function editingDelete($postCategory, $listCategories, $sqlCategories)
    {
        $errors['editing'] = [];
        $editing = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        if (strpos($postCategory['category_name'], '@')) {
            $category['category_id'] = strstr($postCategory['category_name'], '@', true);
            $category['category_name'] = substr(strstr($postCategory['category_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->categoryValidate($category, $listCategories, 'delete');

            $sqlProduct = $this->databaseManager->get('Product');
            $busyCategory = $sqlProduct->searchProducts($category['category_id']);
            $boolBusyCategory = !empty($busyCategory);
            if ($boolBusyCategory) {
                $errors['editing']['category_name'] = '関連する商品があるため削除できません';
            }
        } else {
            $errors['editing']['category_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $sqlCategories->delete($category);
            $listCategories = $sqlCategories->fetchAllCategory();
            $this->convert->convertJson($listCategories, 'category');
            $category = [];
        } else {
            $editing = 'show';
            $editingFieldset = 'disabled';
            $selectFieldset = '';
        }

        return [
            'editing' => $editing,
            'category' => $category,
            'errors' => $errors,
            'listCategories' => $listCategories,
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

        $category = [];
        if (isset($_SESSION['category_id'])) {
            unset($_SESSION['category_id']);
        }
        if (isset($_SESSION['category_name'])) {
            unset($_SESSION['category_name']);
        }

        return [
            'editing' => $editing,
            'category' => $category,
            'errors' => $errors,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
        ];
    }
}
