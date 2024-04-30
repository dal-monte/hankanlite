<?php
class Validate
{
    public function categoryValidate($category, $listCategories = null, $action = null, $listProducts = null)
    {
        $errors = [];

        if (isset($category['category_name'])) {
            $categoryName = $category['category_name'];
        } elseif (isset($category['name'])) {
            $categoryName = $category['name'];
        }

        if (isset($categoryName)) {
            $categoryNull = !strlen($categoryName);
            $categoryDefault = $categoryName === 'カテゴリー名を選択して下さい';
            if ($categoryNull || $categoryDefault) {
                $errors['category_name'] = 'カテゴリー名を入力してください';
            } elseif (mb_strlen($categoryName) > 15) {
                $errors['category_name'] = 'カテゴリー名は15文字以内で入力してください';
            }
        }

        $listExist = !is_null($listCategories);
        $listNotExist = is_null($listCategories);
        $increase = $action === 'increase';
        $update = $action === 'update';
        $select = $action === 'select';
        $delete = $action === 'delete';
        $increaseOrUpdate = $increase || $update;
        $selectOrDelete = $select || $delete;

        if ($listExist && $increaseOrUpdate) {
            foreach ($listCategories as $listCategory) {
                $duplicationName = $categoryName === $listCategory['name'];
                $notSameId = $category['category_id'] !== $listCategory['category_id'];
                if ($duplicationName && $notSameId) {
                    $errors['category_name'] = 'すでに同名のカテゴリーが存在します※重複できません';
                }
            }
        }

        if ($listNotExist && $selectOrDelete) {
            $idList = array_column($listCategories, 'category_id');
            if (!in_array($category['category_id'], $idList)) {
                $errors['category_name'] = '選択肢から選んでください';
            }
        }

        if (isset($listProducts)) {
            $listProductsCategoryId = array_column($listProducts, 'category_id');
            if (!in_array($category['category_id'], $listProductsCategoryId)) {
                $errors['category_name'] = 'このカテゴリーに関連する商品がありません';
            }
        }

        return $errors;
    }

    public function productValidate($product, $listProducts = null, $action = null)
    {
        $errors = [];

        if (isset($product['product_name'])) {
            $productName = $product['product_name'];
        } elseif (isset($product['name'])) {
            $productName = $product['name'];
        }

        if (isset($productName)) {
            $productNull = !strlen($productName);
            $productDefault = $productName === '商品を選択して下さい';
            if ($productNull || $productDefault) {
                $errors['product_name'] = '商品名を入力してください';
            } elseif (mb_strlen($productName) > 30) {
                $errors['product_name'] = '商品名は30文字以内で入力してください';
            }
        }

        if (isset($product['price'])) {
            $priceType = '価格';
            $checkPrice = (int)$product['price'];
        } elseif (isset($product['list_price'])) {
            $priceType = '定価';
            $checkPrice = (int)$product['list_price'];
        }

        if (isset($checkPrice)) {
            if (!strlen($checkPrice) || $checkPrice === 0) {
                $errors['price'] = $priceType . 'を入力してください';
            } elseif (!filter_var($checkPrice, FILTER_VALIDATE_INT)) {
                $errors['price'] = $priceType . 'は半角数字のみで入力して下さい';
            } elseif (mb_strlen($checkPrice) > 13) {
                $errors['price'] = $priceType . 'は1京円以上は非対応です';
            } elseif ($checkPrice < 0) {
                $errors['price'] = $priceType . 'は正の整数を半角数字で入力して下さい';
            }
        }

        if (isset($product['number'])) {
            $product['number'] = (int)$product['number'];
            if (!strlen($product['number'])) {
                $errors['number'] = '個数を入力してください';
            } elseif (!filter_var($product['number'], FILTER_VALIDATE_INT)) {
                $errors['number'] = '個数は半角数字のみで入力して下さい';
            } elseif (mb_strlen($product['number']) > 5) {
                $errors['number'] = '個数は一万個以上は非対応です';
            } elseif ($product['number'] < 0) {
                $errors['number'] = '個数は正の整数を半角数字で入力して下さい';
            }
        }


        $listExist = !is_null($listProducts);
        $increase = $action === 'increase';
        $update = $action === 'update';
        $select = $action === 'select';
        $delete = $action === 'delete';
        $increaseOrUpdate = $increase || $update;
        $selectOrDelete = $select || $delete;

        if ($listExist && $increaseOrUpdate) {
            foreach ($listProducts as $listProduct) {
                if (in_array($productName, $listProduct)) {
                    $errors['product_name'] = 'すでに同名の商品が存在します※重複できません';
                }
            }
        }

        if ($listExist && $selectOrDelete) {
            $idList = array_column($listProducts, 'product_id');
            if (!in_array($product['product_id'], $idList)) {
                $errors['product_name'] = '選択肢から選んでください';
            }
        }

        return $errors;
    }


    public function customerValidate($customer, $listCustomers = null, $action = null)
    {
        $errors = [];


        if (isset($customer['customer_id'])) {
            $customerIdNull = !strlen($customer['customer_id']);
            $customerIdOver = mb_strlen($customer['customer_id']) > 6;
            $customerIdShort = mb_strlen($customer['customer_id']) < 5;
            if ($customerIdNull) {
                $errors['customer_id'] = '顧客IDを入力してください';
            } elseif (!filter_var($customer['customer_id'], FILTER_VALIDATE_INT)) {
                $errors['customer_id'] = '顧客IDは半角数字のみで入力して下さい';
            } elseif ($customer['customer_id'] < 0) {
                $errors['customer_id'] = '顧客IDは正の整数を半角数字で入力して下さい';
            } elseif ($customerIdOver || $customerIdShort) {
                $errors['customer_id'] = '顧客IDは5文字で入力してください';
            }
        }

        if (isset($customer['customer_name'])) {
            $customerName = $customer['customer_name'];
        } elseif (isset($customer['name'])) {
            $customerName = $customer['name'];
        }

        if (isset($customerName)) {
            $customerNull = !strlen($customerName);
            $customerDefault = $customerName === '顧客を選択して下さい';
            if ($customerNull || $customerDefault) {
                $errors['customer_name'] = '顧客名を入力してください';
            } elseif (mb_strlen($customerName) > 30) {
                $errors['customer_name'] = '顧客名は30文字以内で入力してください';
            }
        }

        $listExist = !is_null($listCustomers);
        $listNotExist = is_null($listCustomers);
        $increase = $action === 'increase';
        $select = $action === 'select';
        $delete = $action === 'delete';
        $selectOrDelete = $select || $delete;

        if ($listExist && $increase) {
            foreach ($listCustomers as $listCustomer) {
                if (in_array($customer['customer_id'], $listCustomer)) {
                    $errors['customer_id'] = 'この顧客IDは使用済みです※重複できません';
                }
            }
        }

        if ($listNotExist && $selectOrDelete) {
            $idList = array_column($listCustomers, 'customer_id');
            if (!in_array($customer['customer_id'], $idList)) {
                $errors['customer_name'] = '選択肢から選んでください';
            }
        }

        return $errors;
    }

    public function supplierValidate($supplier, $listSuppliers = null, $action = null)
    {
        $errors = [];

        if (isset($supplier['supplier_id'])) {
            $supplierIdNull = !strlen($supplier['supplier_id']);
            $supplierIdOver = mb_strlen($supplier['supplier_id']) > 6;
            $supplierIdShort = mb_strlen($supplier['supplier_id']) < 5;
            if ($supplierIdNull) {
                $errors['supplier_id'] = '業者IDを入力してください';
            } elseif (!filter_var($supplier['supplier_id'], FILTER_VALIDATE_INT)) {
                $errors['supplier_id'] = '業者IDは半角数字のみで入力して下さい';
            } elseif ($supplier['supplier_id'] < 0) {
                $errors['supplier_id'] = '業者IDは正の整数を半角数字で入力して下さい';
            } elseif ($supplierIdOver || $supplierIdShort) {
                $errors['supplier_id'] = '業者IDは5文字で入力してください';
            }
        }

        if (isset($supplier['supplier_name'])) {
            $supplierName = $supplier['supplier_name'];
        } elseif (isset($supplier['name'])) {
            $supplierName = $supplier['name'];
        }

        if (isset($supplierName)) {
            $supplierNull = !strlen($supplierName);
            $supplierDefault = $supplierName === '業者を選択して下さい';
            if ($supplierNull || $supplierDefault) {
                $errors['supplier_name'] = '業者名を入力してください';
            } elseif (mb_strlen($supplierName) > 30) {
                $errors['supplier_name'] = '業者名は30文字以内で入力してください';
            }
        }

        $listExist = !is_null($listSuppliers);
        $listNotExist = is_null($listSuppliers);
        $increase = $action === 'increase';
        $select = $action === 'select';
        $delete = $action === 'delete';
        $selectOrDelete = $select || $delete;

        if ($listExist && $increase) {
            foreach ($listSuppliers as $listSupplier) {
                if (in_array($supplier['supplier_id'], $listSupplier)) {
                    $errors['supplier_id'] = 'この業者IDは使用済みです※重複できません';
                }
            }
        }

        if ($listNotExist && $selectOrDelete) {
            $idList = array_column($listSuppliers, 'supplier_id');
            if (!in_array($supplier['supplier_id'], $idList)) {
                $errors['supplier_name'] = '選択肢から選んでください';
            }
        }

        return $errors;
    }
    public function contractValidate($contract, $listContracts, $action)
    {
        $errors = [];

        if (isset($contract['contract_id'])) {
            $contractIdNull = !strlen($contract['contract_id']);
            $contractIdOver = mb_strlen($contract['contract_id']) > 7;
            $contractIdShort = mb_strlen($contract['contract_id']) < 6;
            if (isset($contract['contract_id']) && $action === 'increase') {
                if ($contractIdNull) {
                    $errors['contract_id'] = '契約番号を入力してください';
                } elseif (!filter_var($contract['contract_id'], FILTER_VALIDATE_INT)) {
                    $errors['contract_id'] = '契約番号は半角数字のみで入力して下さい';
                } elseif ($contract['contract_id'] < 0) {
                    $errors['contract_id'] = '契約番号は正の整数を半角数字で入力して下さい';
                } elseif ($contractIdOver || $contractIdShort) {
                    $errors['contract_id'] = '契約番号は6文字で入力してください';
                }
            }
        }

        $listExist = !is_null($listContracts);
        $increase = $action === 'increase';
        $search = $action === 'search';
        $select = $action === 'select';
        $delete = $action === 'delete';
        $selectOrDelete = $select || $delete;

        if ($listExist && $increase) {
            foreach ($listContracts as $listContract) {
                if (in_array($contract['contract_id'], $listContract)) {
                    $errors['contract_id'] = 'この契約IDは使用済みです※重複できません';
                }
            }
        }

        if (isset($contract['supplier_id'])) {
            $client = 'supplier_id';
        } elseif (isset($contract['customer_id'])) {
            $client = 'customer_id';
        }

        if ($listExist && $search) {
            $correctContractBool = false;
            foreach ($listContracts as $listContract) {
                if ($contract[$client] === $listContract[$client]) {
                    $correctContractBool = true;
                }
            }
            if (!$correctContractBool) {
                $errors['supplier_name'] = '関連する契約がありません';
            }
        }

        if ($listExist && $selectOrDelete) {
            $correctContractBool = false;
            foreach ($listContracts as $listContract) {
                if ($contract['contract_id'] === $listContract['contract_id'] && $contract[$client] === $listContract[$client]) {
                    $correctContractBool = true;
                }
            }
            if (!$correctContractBool) {
                $errors['supplier_name'] = '選択肢から選んでください';
            }
        }

        return $errors;
    }

    public function contractProductValidate($product, $selectedProducts, $action)
    {
        $errors = [];

        if ($action === 'increase') {
            foreach ($selectedProducts as $selectedProduct) {
                if ((int)$product['product_id'] === $selectedProduct['product_id']) {
                    $errors['product_id'] = 'この商品は既にこの契約に登録されています※重複できません';
                }
            }
        } elseif ($action === 'editing') {
            $idList = array_column($selectedProducts, 'product_id');
            if (!in_array($product['product_id'], $idList)) {
                $errors['product_id'] = '選択肢から選んでください';
            }
        } else {
            throw new HttpNotFoundException();
        }

        return $errors;
    }

    public function userValidate($user, $listUsers = null, $action = null)
    {
        $errors = [];

        if (isset($user['user_id'])) {
            $userIdNull = !strlen($user['user_id']);

            if ($userIdNull) {
                $errors['user_id'] = '社員番号を入力してください';
            } elseif (!mb_strlen($user['user_id']) === 4) {
                $errors['user_id'] = '社員番号は4文字で入力してください';
            }
        }

        if (isset($user['user_name'])) {
            $userName = $user['user_name'];
        } elseif (isset($user['name'])) {
            $userName = $user['name'];
        }

        if (isset($userName)) {
            $userNull = !strlen($userName);

            if ($userNull) {
                $errors['user_name'] = '社員名を入力してください';
            } elseif (mb_strlen($userName) > 30) {
                $errors['user_name'] = '社員名は30文字以内で入力してください';
            }
        }

        if (isset($user['password']) && isset($user['password_again'])) {
            $passwordString = preg_replace('/[a-z]/i', '', $user['password']);
            $passwordInt = preg_replace('/[0-9]/', '', $user['password']);

            if (!strlen($user['password']) || !strlen($user['password_again'])) {
                $errors['password'] = 'パスワードを入力してください';
            } elseif (!($user['password'] === $user['password_again'])) {
                $errors['password'] = '確認欄のパスワードが正しくありません';
            } elseif (mb_strlen($user['password']) >= 20) {
                $errors['password'] = 'パスワードは20文字以内で入力して下さい';
            } elseif (mb_strlen($user['password']) < 6) {
                $errors['password'] = 'パスワードは6文字以上で入力して下さい';
            } elseif (!strlen($passwordString)) {
                $errors['password'] = 'パスワードには最低1文字以上のローマ字を含んで下さい';
            } elseif (!strlen($passwordInt)) {
                $errors['password'] = 'パスワードには最低1文字以上の数字を含んで下さい';
            }
        }

        $listExist = !is_null($listUsers);
        $listNotExist = is_null($listUsers);
        $increase = $action === 'increase';
        $update = $action === 'update';
        $select = $action === 'select';
        $delete = $action === 'delete';
        $increaseOrUpdate = $increase || $update;
        $selectOrDelete = $select || $delete;

        if ($listExist && $increase) {
            foreach ($listUsers as $listUser) {
                if ($user['user_id'] === $listUser['user_id']) {
                    $errors['user_id'] = 'この社員番号は既に使用済みです※重複できません';
                }
            }
        }

        if ($listExist && $selectOrDelete) {
            $idList = array_column($listUsers, 'user_id');
            if (!in_array($user['user_id'], $idList)) {
                $errors['user_name'] = '選択肢から選んでください';
            }
        }

        return $errors;
    }

    public function roleValidate($role, $listRoles)
    {
        $errors = [];

        if (isset($role['role_id'])) {
            $roleNull = !strlen($role['role_id']);
            if ($roleNull) {
                $errors['role_id'] = '役割を選択してください';
            }
        }

        $listExist = !is_null($listRoles);

        if ($listExist) {
            $correctRoleBool = false;
            foreach ($listRoles as $listContract) {
                if ($role['role_name'] === $listContract['role_name'] && $role['role_id'] === $listContract['role_id']) {
                    $correctRoleBool = true;
                }
            }
            if (!$correctRoleBool) {
                $errors['role'] = '役割は選択肢から選んでください';
            }
        }

        return $errors;
    }
}
