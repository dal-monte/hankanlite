<?php

class Controller
{
    protected $actionName;
    protected $request;
    protected $databaseManager;
    protected $navbar;
    protected $controllerName;
    protected $validate;
    protected $convert;
    protected $taxRate;
    protected $token;
    protected $reCaptcha;

    protected $mainDatabase;

    protected $companyId;

    public function __construct($application)
    {
        $this->request = $application->getRequest();
        $this->databaseManager = $application->getDatabaseManager();
        $this->navbar = $application->getLibrary()->navbarName;
        $this->taxRate = $application->getLibrary()->taxRate;
        $this->validate = $application->getValidate();
        $this->convert = $application->getConvert();
        $this->token = $application->getToken();
        $this->reCaptcha = $application->getReCaptcha();
        $this->mainDatabase = $application->getMainDatabase();
    }

    public function run($action)
    {
        $this->actionName = $action;

        if (!method_exists($this, $action)) {
            throw new HttpNotFoundException();
        }

        $this->controllerName = substr(get_class($this), 0, -10);

        $content = $this->$action();
        return $content;
    }

    protected function judgeReCaptcha($reCaptchaKey)
    {
        if (isset($reCaptchaKey)) {
            if (isset($_POST['g-recaptcha-response'])) {
                $captchaResponse = $_POST['g-recaptcha-response'];
            } else {
                throw new HttpNotFoundException();
            }

            //Bot確認。0.5以下はBotと判断
            $recapResult = $this->reCaptcha->getResultReCaptcha($captchaResponse);
            if ($recapResult->score < 0.5) {
                throw new HttpNotFoundException();
            }
        }
    }

    protected function render($variables = [], $template = null, $layout = 'layout')
    {
        $view = new View(__DIR__ . '/../views');

        if (is_null($template)) {
            $template = $this->actionName;
        }

        $controllerName = lcfirst($this->controllerName);  // 右記のように変換される　shuffle
        $path = $controllerName . '/' . $template;  // 右記のように変換される　shuffle/index
        return $view->render($path, $variables, $layout);
    }

    protected function securityCheck($navElement, $fromNavbar = null)
    {
        $navbar = $this->navbar;
        $role = "not";

        if (isset($fromNavbar) && $fromNavbar === "navbar") {
            $_SESSION['token'] = null;
            $token = $this->csrfToken();

            if (isset($_SESSION["role"])) {
                $role = $_SESSION["role"];
            }
            if (!in_array($navElement, $navbar[$role])) {
                throw new HttpNotFoundException();
            }

            return [
                'token' => $token,
                'navbar' => $navbar[$role],
            ];
        } else {
            if (!$this->request->isPost()) {
                throw new HttpNotFoundException();
            }
            $token = $this->csrfToken();

            if (isset($_SESSION["role"])) {
                $role = $_SESSION["role"];
            }
            if (!in_array($navElement, $navbar[$role])) {
                throw new HttpNotFoundException();
            }

            return $token;
        }
    }

    protected function csrfToken()
    {
        if (isset($_SESSION['token'])) {
            $tokenBool = false;

            if (isset($_POST['token'])) {
                $beforeToken = $_SESSION['token'];
                $afterToken = $_POST['token'];
                $tokenBool = $this->token->token($beforeToken, $afterToken);
            } else {
                throw new HttpNotFoundException();
            }

            if ($tokenBool) {
                $newToken = $this->token->token();
                $_SESSION['token'] = $newToken;

                return $newToken;
            } elseif (!$tokenBool) {
                throw new HttpNotFoundException();
            }
        } else {
            $token = $this->token->token();
            $_SESSION['token'] = $token;
            return $token;
        }
    }

    private function judgeContractType($contract, $userId)
    {
        if (isset($_SESSION['now_user_id'])) {
            $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        if ($contract['contract_type'] === 'purchase') {
            $contract['purchase_contract_id'] = $contract['contract_id'];
            $sqlPurchaseProducts = $this->databaseManager->get('PurchaseProduct', $companyId);
            $listPurchaseProducts = $sqlPurchaseProducts->fetchPurchaseProduct($contract);
            if (is_null($listPurchaseProducts)) {
                $listPurchaseProducts = [];
            }
            $this->convert->convertJson($listPurchaseProducts, 'purchaseProduct', $userId);
            $listSelectedProducts = $listPurchaseProducts;
        } elseif ($contract['contract_type'] === 'sales') {
            $contract['sales_contract_id'] = $contract['contract_id'];
            $sqlSalesProducts = $this->databaseManager->get('SalesProduct', $companyId);
            $listSalesProducts = $sqlSalesProducts->fetchSalesProduct($contract);
            if (is_null($listSalesProducts)) {
                $listSalesProducts = [];
            }
            $this->convert->convertJson($listSalesProducts, 'salesProduct', $userId);
            $listSelectedProducts = $listSalesProducts;
        } else {
            throw new HttpNotFoundException();
        }

        $negativeQuantities = null;
        foreach ($listSelectedProducts as $listSelectedProduct) {
            if ($listSelectedProduct['quantity'] < 0) {
                $negativeQuantities[] = $listSelectedProduct['product_name'];
            }
        }

        return [
            'contract' => $contract,
            'listSelectedProducts' => $listSelectedProducts,
            'negativeQuantities' => $negativeQuantities,
        ];
    }

    protected function tableRender($actionName, $controllerName, $contract, $userId, $token, $postData = null)
    {
        $errors[] = [];

        $token;

        if (isset($_SESSION['now_user_id'])) {
            $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        $sqlCategories = $this->databaseManager->get('Category', $companyId);
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts = $this->databaseManager->get('Product', $companyId);
        $listProducts = $sqlProducts->fetchAllProduct();

        $usedCategories = array_column($listProducts, 'category_id');
        foreach ($listCategories as $listCategory) {
            if (in_array($listCategory['category_id'], $usedCategories, true)) {
                $listUsedCategories[] = $listCategory;
            }
        }

        $judgeContractType = $this->judgeContractType($contract, $userId);
        extract($judgeContractType);

        if (isset($postData['tableEditingSelect'])) {
            $tableEditingSelect = $this->tableEditingSelect($postData, $listProducts, $listSelectedProducts);
            extract($tableEditingSelect);
        } elseif (isset($postData['tableEditingDelete'])) {
            $tableEditingDelete = $this->tableEditingDelete($postData, $listProducts, $listSelectedProducts, $contract, $companyId);
            extract($tableEditingDelete);
        } elseif (isset($postData['tableEditingUpdate'])) {
            $tableEditingUpdate = $this->tableEditingUpdate($postData, $contract, $listSelectedProducts, $companyId);
            extract($tableEditingUpdate);
        } elseif (isset($postData['tableIncreaseSearch'])) {
            $tableIncreaseSearch = $this->tableIncreaseSearch($postData, $listProducts, $listCategories);
            extract($tableIncreaseSearch);
        } elseif (isset($postData['tableIncreaseSelect'])) {
            $tableIncreaseSelect = $this->tableIncreaseSelect($postData, $listProducts, $contract, $listSelectedProducts);
            extract($tableIncreaseSelect);
        } elseif (isset($postData['tableIncrease'])) {
            $tableIncrease = $this->tableIncrease($postData, $contract, $companyId);
            extract($tableIncrease);
        }

        $judgeContractType = $this->judgeContractType($contract, $userId);
        extract($judgeContractType);

        // テーブルの挿入
        ob_start();
        $actionName; //テーブルのID指定
        $controllerName = lcfirst($controllerName); //submitの遷移先情報用
        require __DIR__ . "../../views/table.php";
        $tableOutput['table'] = ob_get_clean();

        return $tableOutput;
    }

    private function tableEditingSelect($postData, $listProducts, $listSelectedProducts)
    {
        $errors['editingTable'] = [];
        $editingProduct = null;

        // POSTに選択した商品名が存在していて、正しい形式である場合にバリデーションをかける
        $checkExistProduct = isset($postData['product_name']);
        $checkCorrectnessProduct = strpos($postData['product_name'], '@');
        if ($checkExistProduct && $checkCorrectnessProduct) {
            $product['product_id'] = strstr($postData['product_name'], '@', true);
            $product['product_name'] = substr(strstr($postData['product_name'], '@', false), 1);
            $errors['editingTable'] = $errors['editingTable'] + $this->validate->productValidate($product, $listProducts, 'select');
            $errors['editingTable'] = $errors['editingTable'] + $this->validate->contractProductValidate($product, $listSelectedProducts, 'editing');
            foreach ($listSelectedProducts as $listSelectedProduct) {
                if ((int)$product['product_id'] === $listSelectedProduct['product_id']) {
                    $editingProduct = $listSelectedProduct;
                }
            }
        } else {
            $errors['editingTable']['product_name'] = '商品を選択肢から選んでください';
        }

        if (!count($errors['editingTable'])) {
            $tableOutput['product'] = $editingProduct;
            $selectEditingFieldset = 'disabled';
            $editingFieldset = 'able';
            $selector = 'disabled';
            $editing = 'show';
        } else {
            $editing = 'show';
            $selectEditingFieldset = null;
            $editingFieldset = null;
            $selector = null;
            $tableOutput['product'] = null;
        }

        return [
            'errors' => $errors,
            'editingProduct' => $editingProduct,
            'tableOutput' => $tableOutput,
            'selectEditingFieldset' => $selectEditingFieldset,
            'editingFieldset' => $editingFieldset,
            'selector' => $selector,
            'editing' => $editing,
        ];
    }

    private function tableEditingDelete($postData, $listProducts, $listSelectedProducts, $contract, $companyId)
    {
        $errors['editingTable'] = [];

        $product = null;

        $checkExistProduct = isset($postData['product_name']);
        $checkCorrectnessProduct = strpos($postData['product_name'], '@');

        if ($checkExistProduct && $checkCorrectnessProduct) {
            $product['product_id'] = strstr($postData['product_name'], '@', true);
            $product['product_name'] = substr(strstr($postData['product_name'], '@', false), 1);
            $errors['editingTable'] = $errors['editingTable'] + $this->validate->productValidate($product, $listProducts, 'select');
            $errors['editingTable'] = $errors['editingTable'] + $this->validate->contractProductValidate($product, $listSelectedProducts, 'editing');
        } else {
            $errors['editingTable']['product_name'] = '商品を選択肢から選んでください';
        }

        if (!count($errors['editingTable'])) {

            // 削除する契約に含まれていた商品毎の個数を変数に入れて契約削除と在庫個数調整を行う
            foreach ($listSelectedProducts as $listSelectedProduct) {
                if ((int)$product['product_id'] === $listSelectedProduct['product_id']) {
                    $editingProduct = $listSelectedProduct;
                }
            }
            if ($contract['contract_type'] === 'purchase') {
                $sqlPurchaseProducts = $this->databaseManager->get('PurchaseProduct', $companyId);
                $sqlStock = $this->databaseManager->get('Stock', $companyId);
                $sqlPurchaseProducts->delete($editingProduct);
                $sqlStock->decrease($editingProduct);
            } elseif ($contract['contract_type'] === 'sales') {
                $sqlSalesProducts = $this->databaseManager->get('SalesProduct', $companyId);
                $sqlStock = $this->databaseManager->get('Stock', $companyId);
                $sqlSalesProducts->delete($editingProduct);
                $sqlStock->increase($editingProduct);
            }
            $editingProduct = [];
            $editing = null;
        } else {
            $editing = 'show';
            $editingProduct = $product;
        }

        return [
            'editingProduct' => $editingProduct,
            'editing' => $editing,
            'errors' => $errors,
        ];
    }

    private function tableEditingUpdate($postData, $contract, $listSelectedProducts, $companyId)
    {
        $errors['editingTable'] = [];

        if (isset($contract['product']['product_id'])) {
            $editingProduct = $contract['product'];
        } else {
            throw new HttpNotFoundException();
        }
        $checkExistProductPrice = isset($postData['editing_price']);
        $checkExistProductNumber = isset($postData['number']);

        if ($checkExistProductPrice && $checkExistProductNumber) {
            $editingProduct = [
                'product_id' => $contract['product']['product_id'],
                'product_name' => $contract['product']['product_name'],
                'price' => trim(str_replace(',', '', $postData['editing_price'])),
                'number' => trim($postData['number']),
                'list_price' => $contract['product']['list_price'],
                'category_name' => $contract['product']['category_name'],
                'category_id' => $contract['product']['category_id'],
                'quantity' => $contract['product']['quantity'],
            ];
            if ($contract['contract_type'] === 'purchase') {
                $editingProduct['purchase_contract_id'] = $contract['purchase_contract_id'];
            } elseif ($contract['contract_type'] === 'sales') {
                $editingProduct['sales_contract_id'] = $contract['sales_contract_id'];
            }
            $errors['editingTable'] = $errors['editingTable'] + $this->validate->productValidate($editingProduct);
        } else {
            throw new HttpNotFoundException();
        }

        if (!count($errors['editingTable'])) {

            // 契約の内容変更する前と後の商品名と商品毎の個数変化を変数に入れて商品増減と在庫個数調整を行う
            foreach ($listSelectedProducts as $listSelectedProduct) {
                if ($listSelectedProduct['product_id'] === $editingProduct['product_id']) {
                    $selectedProduct = $listSelectedProduct;
                }
            }
            $updateStock['number'] = $editingProduct['number'] - $selectedProduct['number'];
            if ($contract['contract_type'] === 'purchase') {
                $sqlPurchaseProducts = $this->databaseManager->get('PurchaseProduct', $companyId);
                $sqlStock = $this->databaseManager->get('Stock', $companyId);
                $sqlPurchaseProducts->update($editingProduct);
                $updateStock['product_id'] = $editingProduct['product_id'];
                $sqlStock->increase($updateStock);
            } elseif ($contract['contract_type'] === 'sales') {
                $sqlSalesProducts = $this->databaseManager->get('SalesProduct', $companyId);
                $sqlStock = $this->databaseManager->get('Stock', $companyId);
                $sqlSalesProducts->update($editingProduct);
                $updateStock['product_id'] = $editingProduct['product_id'];
                $sqlStock->decrease($updateStock);
            }
            $editingProduct = [];
            $selectEditingFieldset = null;
            $editingFieldset = null;
            $selector = null;
            $editing = null;
        } else {
            $selectEditingFieldset = 'disabled';
            $editingFieldset = 'able';
            $selector = 'disabled';
            $editing = 'show';
        }

        return [
            'selectEditingFieldset' => $selectEditingFieldset,
            'editingFieldset' => $editingFieldset,
            'selector' => $selector,
            'editingProduct' => $editingProduct,
            'editing' => $editing,
            'errors' => $errors,
        ];
    }

    private function tableIncreaseSearch($postData, $listProducts, $listCategories)
    {
        $errors['increaseTable'] = [];

        $checkExistCategory = isset($postData['category_name']);
        $checkCorrectnessCategory = strpos($postData['category_name'], '@');

        if ($checkExistCategory && $checkCorrectnessCategory) {
            $category['category_name'] = $postData['category_name'];
            $category['category_id'] = (int)strstr($category['category_name'], '@', true);
            $category['category_name'] = substr(strstr($category['category_name'], '@', false), 1);
            $errors['increaseTable'] = $this->validate->categoryValidate($category, $listCategories, 'select', $listProducts);
        } else {
            $errors['increaseTable']['category_name'] = 'カテゴリーを選択肢から選んでください';
        }

        if (!count($errors['increaseTable'])) {
            $tableOutput['product'] = $category;

            $checkedProducts = [];

            foreach ($listProducts as $listProduct) {
                if ($category['category_id'] === $listProduct['category_id']) {
                    $checkedProducts[] = $listProduct;
                };
            }
            $listProducts = $checkedProducts;
            $increaseProduct = $category;
            $searchIncreaseFieldset = 'disabled';
            $selector = 'disabled';
            $selectIncreaseFieldset = 'able';
            $increase = 'show';
        } else {
            $searchIncreaseFieldset = '';
            $increase = 'show';
            $tableOutput['product'] = null;
            $selector = null;
            $selectIncreaseFieldset = null;
        }

        return [
            'selectIncreaseFieldset' => $selectIncreaseFieldset,
            'searchIncreaseFieldset' => $searchIncreaseFieldset,
            'selector' => $selector,
            'increaseProduct' => $increaseProduct,
            'listProducts' => $listProducts,
            'increase' => $increase,
            'errors' => $errors,
            'tableOutput' => $tableOutput,
        ];
    }

    private function tableIncreaseSelect($postData, $listProducts, $contract, $listSelectedProducts)
    {
        $errors['increaseTable'] = [];

        if (!isset($listSelectedProducts)) {
            throw new HttpNotFoundException();
        }

        $checkExistProduct = isset($postData['product_name']);
        $checkCorrectnessProduct = strpos($postData['product_name'], '@');

        if ($checkExistProduct && $checkCorrectnessProduct) {
            $product['product_name'] = $postData['product_name'];
            $product['product_id'] = (int)strstr($product['product_name'], '@', true);
            $product['product_name'] = substr(strstr($product['product_name'], '@', false), 1);
            $errors['increaseTable'] = $errors['increaseTable'] + $this->validate->productValidate($product, $listProducts, 'select');
            $errors['increaseTable'] = $errors['increaseTable'] + $this->validate->contractProductValidate($product, $listSelectedProducts, 'increase');
        } else {
            $errors['increaseTable']['product_name'] = '商品を選択肢から選んでください';
        }

        if (!count($errors['increaseTable'])) {

            $checkedProduct = [];
            foreach ($listProducts as $listProduct) {
                if ($product['product_id'] === $listProduct['product_id']) {
                    $checkedProduct = $listProduct;
                };
            }
            $increaseProduct = $checkedProduct;

            $tableOutput['product'] = $increaseProduct;
            $searchIncreaseFieldset = 'disabled';
            $selector = 'disabled';
            $increaseFieldset = '';
            $increase = 'show';
        } else {
            if (!isset($increaseProduct['product_id'])) {
                $increaseProduct = $contract['product'];
            }

            $checkedProducts = [];
            foreach ($listProducts as $listProduct) {
                if ($increaseProduct['category_id'] === $listProduct['category_id']) {
                    $checkedProducts[] = $listProduct;
                };
            }
            $listProducts = $checkedProducts;
            $increaseFieldset = null;
            $searchIncreaseFieldset = null;
            $selector = null;
            $increase = 'show';
            $tableOutput['product'] = null;
        }

        return [
            'searchIncreaseFieldset' => $searchIncreaseFieldset,
            'increaseFieldset' => $increaseFieldset,
            'selector' => $selector,
            'increaseProduct' => $increaseProduct,
            'increase' => $increase,
            'errors' => $errors,
            'tableOutput' => $tableOutput,
            'listProducts' => $listProducts,
        ];
    }

    private function tableIncrease($postData, $contract, $companyId)
    {
        $errors['increaseTable'] = [];

        if (isset($contract['product']['product_id'])) {
            $increaseProduct = $contract['product'];
        } else {
            throw new HttpNotFoundException();
        }
        $checkExistProductPrice = isset($postData['increase_price']);
        $checkExistProductNumber = isset($postData['number']);

        if ($checkExistProductPrice && $checkExistProductNumber) {
            $increaseProduct = [
                'product_id' => $contract['product']['product_id'],
                'product_name' => $contract['product']['product_name'],
                'price' => trim(str_replace(',', '', $postData['increase_price'])),
                'number' => trim($postData['number']),
                'list_price' => $contract['product']['list_price'],
                'category_name' => $contract['product']['category_name'],
                'category_id' => $contract['product']['category_id'],
                'quantity' => $contract['product']['quantity'],
            ];
            if ($contract['contract_type'] === 'purchase') {
                $increaseProduct['purchase_contract_id'] = $contract['purchase_contract_id'];
            } elseif ($contract['contract_type'] === 'sales') {
                $increaseProduct['sales_contract_id'] = $contract['sales_contract_id'];
            }
            $errors['increaseTable'] = $errors['increaseTable'] + $this->validate->productValidate($increaseProduct);
        } else {
            throw new HttpNotFoundException();
        }

        if (!count($errors['increaseTable'])) {
            if ($contract['contract_type'] === 'purchase') {
                $sqlPurchaseProducts = $this->databaseManager->get('PurchaseProduct', $companyId);
                $sqlStock = $this->databaseManager->get('Stock', $companyId);
                $sqlPurchaseProducts->insert($increaseProduct);
                $sqlStock->increase($increaseProduct);
            } elseif ($contract['contract_type'] === 'sales') {
                $sqlSalesProducts = $this->databaseManager->get('SalesProduct', $companyId);
                $sqlStock = $this->databaseManager->get('Stock', $companyId);
                $sqlSalesProducts->insert($increaseProduct);
                $sqlStock->decrease($increaseProduct);
            }
            $increaseProduct = [];
            $searchIncreaseFieldset = null;
            $selector = null;
            $increaseFieldset = null;
            $increase = null;
        } else {
            $searchIncreaseFieldset = 'disabled';
            $selector = 'disabled';
            $increaseFieldset = 'able';
            $increase = 'show';
        }

        return [
            'searchIncreaseFieldset' => $searchIncreaseFieldset,
            'increaseFieldset' => $increaseFieldset,
            'selector' => $selector,
            'increaseProduct' => $increaseProduct,
            'increase' => $increase,
            'errors' => $errors,
        ];
    }
}
