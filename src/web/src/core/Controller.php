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

        $controllerName = strtolower($this->controllerName);  // 右記のように変換される　shuffle
        $path = $controllerName . '/' . $template;  // 右記のように変換される　shuffle/index
        return $view->render($path, $variables, $layout);
    }

    protected function createToken()
    {
        $token = $this->token->token();
        $_SESSION['token'] = $token;
        return $token;
    }

    protected function judgeToken()
    {
        $tokenBool = false;

        if (isset($_POST['token']) && isset($_SESSION['token'])) {
            $beforeToken = $_SESSION['token'];
            $afterToken = $_POST['token'];
            $tokenBool = $this->token->token($beforeToken, $afterToken);
        } else {
            throw new HttpNotFoundException();
        }

        if ($tokenBool = true) {
            return $afterToken;
        } elseif ($tokenBool = false) {
            throw new HttpNotFoundException();
        }
    }

    protected function tableRender($actionName, $controllerName, $contract, $userId, $postData = null)
    {
        $errors[] = [];

        $sqlCategories = $this->databaseManager->get('Category');
        $listCategories = $sqlCategories->fetchAllCategory();

        $sqlProducts = $this->databaseManager->get('Product');
        $listProducts = $sqlProducts->fetchAllProduct();

        $sqlStock = $this->databaseManager->get('Stock');

        $usedCategories = array_column($listProducts, 'category_id');
        foreach ($listCategories as $listCategory) {
            if (in_array($listCategory['category_id'], $usedCategories, true)) {
                $listUsedCategories[] = $listCategory;
            }
        }

        if ($contract['contract_type'] === 'purchase') {
            $contract['purchase_contract_id'] = $contract['contract_id'];
            $sqlPurchaseProducts = $this->databaseManager->get('PurchaseProduct');
            $listPurchaseProducts = $sqlPurchaseProducts->fetchPurchaseProduct($contract);
            if (is_null($listPurchaseProducts)) {
                $listPurchaseProducts = [];
            }
            $this->convert->convertJson($listPurchaseProducts, 'purchaseProduct', $userId);
            $listSelectedProducts = $listPurchaseProducts;
        }

        if ($contract['contract_type'] === 'sales') {
            $contract['sales_contract_id'] = $contract['contract_id'];
            $sqlSalesProducts = $this->databaseManager->get('SalesProduct');
            $listSalesProducts = $sqlSalesProducts->fetchSalesProduct($contract);
            if (is_null($listSalesProducts)) {
                $listSalesProducts = [];
            }
            $this->convert->convertJson($listSalesProducts, 'salesProduct', $userId);
            $listSelectedProducts = $listSalesProducts;

            $negativeQuantities = null;
            foreach ($listSelectedProducts as $listSelectedProduct) {
                if ($listSelectedProduct['quantity'] < 0) {
                    $negativeQuantities[] = $listSelectedProduct['product_name'];
                }
            }
        }

        // テーブルの入力欄の表示非表示コントロール
        if (isset($postData['tableEditingSelect'])) {
            $errors['editingTable'] = [];

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
            }
        } elseif (isset($postData['tableEditingDelete'])) {

            $errors['editingTable'] = [];

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

                foreach ($listSelectedProducts as $listSelectedProduct) {
                    if ((int)$product['product_id'] === $listSelectedProduct['product_id']) {
                        $editingProduct = $listSelectedProduct;
                    }
                }

                $sqlPurchaseProducts->delete($editingProduct);
                if ($contract['contract_type'] === 'purchase') {
                    $sqlStock->decrease($editingProduct);
                    $listSelectedProducts = $sqlPurchaseProducts->fetchPurchaseProduct($contract);
                    $this->convert->convertJson($listSelectedProducts, 'purchaseProduct', $userId);
                } elseif ($contract['contract_type'] === 'sales') {
                    $sqlStock->increase($editingProduct);
                    $listSelectedProducts = $sqlSalesProducts->fetchSalesProduct($contract);
                    $this->convert->convertJson($listSelectedProducts, 'salesProduct', $userId);
                }
                $editingProduct = [];
                $tableOutput['product'] = [];
            } else {
                $editing = 'show';
            }
        } elseif (isset($postData['tableEditing'])) {

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

                foreach ($listSelectedProducts as $listSelectedProduct) {
                    if ($listSelectedProduct['product_id'] === $editingProduct['product_id']) {
                        $selectedProduct = $listSelectedProduct;
                    }
                }

                $updateStock['number'] = $editingProduct['number'] - $selectedProduct['number'];

                if ($contract['contract_type'] === 'purchase') {
                    $sqlPurchaseProducts->update($editingProduct);
                    $updateStock['product_id'] = $editingProduct['product_id'];
                    $sqlStock->increase($updateStock);
                    $listSelectedProducts = $sqlPurchaseProducts->fetchPurchaseProduct($contract);
                    $this->convert->convertJson($listSelectedProducts, 'purchaseProduct', $userId);
                } elseif ($contract['contract_type'] === 'sales') {
                    $sqlSalesProducts->update($editingProduct);
                    $updateStock['product_id'] = $editingProduct['product_id'];
                    $sqlStock->decrease($updateStock);
                    $listSelectedProducts = $sqlSalesProducts->fetchSalesProduct($contract);
                    $this->convert->convertJson($listSelectedProducts, 'salesProduct', $userId);
                }
                $editingProduct = [];
                $tableOutput['product'] = [];
            } else {
                $selectEditingFieldset = 'disabled';
                $editingFieldset = 'able';
                $selector = 'disabled';
                $editing = 'show';
            }
        } elseif (isset($postData['tableIncreaseSearch'])) {
            $errors['increaseTable'] = [];

            $checkExistCategory = isset($postData['category_name']);
            $checkCorrectnessCategory = strpos($postData['category_name'], '@');

            if ($checkExistCategory && $checkCorrectnessCategory) {
                $category['category_name'] = $postData['category_name'];
                $category['category_id'] = strstr($category['category_name'], '@', true);
                $category['category_name'] = substr(strstr($category['category_name'], '@', false), 1);
                $errors['increaseTable'] = $this->validate->categoryValidate($category, $listCategories, 'select', $listProducts);
            } else {
                $errors['increaseTable']['category_name'] = 'カテゴリーを選択肢から選んでください';
            }

            if (!count($errors['increaseTable'])) {
                $tableOutput['product'] = $category;
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
            }
        } elseif (isset($postData['tableIncreaseSelect'])) {
            $errors['increaseTable'] = [];

            if (!isset($listSelectedProducts)) {
                throw new HttpNotFoundException();
            }

            $checkExistProduct = isset($postData['product_name']);
            $checkCorrectnessProduct = strpos($postData['product_name'], '@');

            if ($checkExistProduct && $checkCorrectnessProduct) {
                $product['product_name'] = $postData['product_name'];
                $product['product_id'] = strstr($product['product_name'], '@', true);
                $product['product_name'] = substr(strstr($product['product_name'], '@', false), 1);
                $errors['increaseTable'] = $errors['increaseTable'] + $this->validate->productValidate($product, $listProducts, 'select');
                $errors['increaseTable'] = $errors['increaseTable'] + $this->validate->contractProductValidate($product, $listSelectedProducts, 'increase');
            } else {
                $errors['increaseTable']['product_name'] = '商品を選択肢から選んでください';
            }

            if (!count($errors['increaseTable'])) {
                foreach ($listProducts as $listProduct) {
                    if ($product['product_id'] === $listProduct['product_id']) {
                        $checkedProduct = $listProduct;
                        $checkedProduct['product_name'] = $checkedProduct['name'];
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
                foreach ($listProducts as $listProduct) {
                    if ($increaseProduct['category_id'] === $listProduct['category_id']) {
                        $checkedProducts[] = $listProduct;
                    };
                }
                $listProducts = $checkedProducts;

                $searchIncreaseFieldset = 'disabled';
                $selector = 'disabled';
                $selectIncreaseFieldset = 'able';
                $increase = 'show';
            }
            $searchIncreaseFieldset = 'disabled';
            $selector = 'disabled';
            $increaseFieldset = '';
            $increase = 'show';
        } elseif (isset($postData['tableIncrease'])) {
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
                    $sqlPurchaseProducts->insert($increaseProduct);
                    $sqlStock->increase($increaseProduct);
                    $listSelectedProducts = $sqlPurchaseProducts->fetchPurchaseProduct($contract);
                    $this->convert->convertJson($listSelectedProducts, 'purchaseProduct', $userId);
                } elseif ($contract['contract_type'] === 'sales') {
                    $sqlSalesProducts->insert($increaseProduct);
                    $sqlStock->decrease($increaseProduct);
                    $listSelectedProducts = $sqlSalesProducts->fetchSalesProduct($contract);
                    $this->convert->convertJson($listSelectedProducts, 'salesProduct', $userId);
                }
                $increaseProduct = [];
                $tableOutput['product'] = [];
            } else {
                $searchIncreaseFieldset = 'disabled';
                $selector = 'disabled';
                $increaseFieldset = '';
                $increase = 'show';
            }
        }

        // テーブルの挿入
        ob_start();
        $actionName; //テーブルのID指定
        $controllerName = lcfirst($controllerName); //submitの遷移先情報用
        require __DIR__ . "../../views/table.php";
        $tableOutput['table'] = ob_get_clean();

        return $tableOutput;
    }
}
