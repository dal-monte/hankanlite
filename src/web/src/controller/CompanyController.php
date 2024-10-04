<?php

class CompanyController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("companies");

        if (isset($_SESSION['increase_company_id'])) {
            unset($_SESSION['increase_company_id']);
        }
        if (isset($_SESSION['increase_company_name'])) {
            unset($_SESSION['increase_company_name']);
        }

        if (isset($_SESSION['editing_company_id'])) {
            unset($_SESSION['editing_company_id']);
        }
        if (isset($_SESSION['editing_company_name'])) {
            unset($_SESSION['editing_company_name']);
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompaniesTable = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompaniesTable->fetchCompany();
        // 表にデータを挿入
        $this->convert->convertJson($listCompanies, 'company');

        return $this->render([
            'title' => '企業の登録',
            'errors' => [],
            'companies' => $listCompanies,
            'token' => $token,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("companies");

        if (isset($_SESSION['editing_company_id'])) {
            unset($_SESSION['editing_company_id']);
        }
        if (isset($_SESSION['editing_company_name'])) {
            unset($_SESSION['editing_company_name']);
        }

        $errors['increase'] = [];
        $increaseUser = null;

        if (isset($_POST['create'])) {
            $createDatabase = $this->increaseDatabase();
            extract($createDatabase);
        } elseif (isset($_POST['increase'])) {
            $insertUser = $this->increaseUser();
            extract($insertUser);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompaniesTable = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompaniesTable->fetchCompany();
        // 表にデータを挿入
        $this->convert->convertJson($listCompanies, 'company');

        return $this->render([
            'title' => '企業の登録',
            'errors' => $errors,
            'companies' => $listCompanies,
            'increaseCompany' => $increaseCompany,
            'increaseUser' => $increaseUser,
            'increaseSession' => $increaseSession,
            'createFieldset' => $createFieldset,
            'increaseFieldset' => $increaseFieldset,
            'token' => $token,
        ], 'index');
    }

        private function increaseDatabase()
    {
        $increaseSession = 'show';
        $increaseFieldset = 'disabled';
        $createFieldset = '';
        $errors['increase'] = [];

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompanies = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompanies->fetchCompany();

        if (isset($_SESSION['company_id'])) {
            unset($_SESSION['company_id']);
        }
        if (isset($_SESSION['company_name'])) {
            unset($_SESSION['company_name']);
        }

        if (isset($_POST['company_id']) && isset($_POST['company_name'])) {
            $postCompany = [
                'company_id' => trim($_POST['company_id']),
                'company_name' => trim($_POST['company_name']),
            ];
        } else {
            throw new HttpNotFoundException();
        }

        $errors['increase'] = $errors['increase'] + $this->validate->companyValidate($postCompany, $listCompanies, 'increase');
        $increaseCompany = $postCompany;

        if (!count($errors['increase'])) {
            $increaseFieldset = '';
            $createFieldset = 'disabled';
            $databaseCompany = "`" . $increaseCompany['company_id'] . "`";
            $sqlNewDatabase = $this->databaseManager->get('Company');
            $sqlNewDatabase->createDatabase($databaseCompany);
            $sqlCompaniesTable = $this->databaseManager->get('Company', $this->mainDatabase);
            $sqlCompaniesTable->insert($postCompany);
            $sqlNewTable = $this->databaseManager->get('Company', $postCompany['company_id']);
            $sqlNewTable->createTable();
            $_SESSION['company_id'] = $increaseCompany['company_id'];
            $_SESSION['company_name'] = $increaseCompany['company_name'];
        }

        return [
            'increaseSession' => $increaseSession,
            'increaseFieldset' => $increaseFieldset,
            'createFieldset' => $createFieldset,
            'increaseCompany' => $increaseCompany,
            'errors' => $errors,
        ];
    }


        private function increaseUser()
    {
        $increaseSession = 'show';
        $increaseFieldset = '';
        $createFieldset = 'disabled';
        $errors['increase'] = [];

        if (isset($_SESSION['company_id'])) {
            $increaseCompany['company_id'] = $_SESSION['company_id'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION['company_name'])) {
            $increaseCompany['company_name'] = $_SESSION['company_name'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['user_id']) && isset($_POST['user_name'])) {
            $user = [
                'user_id_before' => trim($_POST['user_id']),
                'user_name' => trim($_POST['user_name']),
            ];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['password']) && isset($_POST['password_again'])) {
            $userPassword = [
                'password' => trim($_POST['password']),
                'password_again' => trim($_POST['password_again']),
            ];
            $user = $user + $userPassword;
            $userPassword = '';
        } else {
            throw new HttpNotFoundException();
        }

        $user['user_id'] = $increaseCompany['company_id'] . $user['user_id_before'];
        $checkCompanyUsers = $increaseCompany['company_id'];

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlUsers = $this->databaseManager->get('User', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listUsers = $sqlUsers->fetchUser($checkCompanyUsers);

        $errors['increase'] = $errors['increase'] + $this->validate->userValidate($user, $listUsers, 'increase');

        if (!count($errors['increase'])) {
            if (isset($_SESSION['increase_company_id'])) {
                unset($_SESSION['increase_company_id']);
            }
            if (isset($_SESSION['increase_company_name'])) {
                unset($_SESSION['increase_company_name']);
            }

            $user['role_id'] = '1';
            $user['password_again'] = '';
            $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
            $sqlUsers->insert($user);
            $user = [];
            $increaseSession = '';
        } else {
            $user['password'] = '';
            $user['password_again'] = '';
            $user['user_id'] = $user['user_id_before'];
        }

        return [
            'increaseSession' => $increaseSession,
            'increaseFieldset' => $increaseFieldset,
            'createFieldset' => $createFieldset,
            'increaseCompany' => $increaseCompany,
            'increaseUser' => $user,
            'errors' => $errors,
        ];
    }




    public function editing()
    {
        session_start();

        $token = $this->securityCheck("companies");

        if (isset($_SESSION['increase_company_id'])) {
            unset($_SESSION['increase_company_id']);
        }
        if (isset($_SESSION['increase_company_name'])) {
            unset($_SESSION['increase_company_name']);
        }

        $editingCompany = [];

        $errors['editing'] = [];

        if (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect();
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate();
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete();
            extract($editingDelete);
        } else {
            $editingElse = $this->editingElse();
            extract($editingElse);
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompaniesTable = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompaniesTable->fetchCompany();
        // 表にデータを挿入
        $this->convert->convertJson($listCompanies, 'company');

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'editingCompany' => $editingCompany,
            'editingSession' => $editingSession,
            'companies' => $listCompanies,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'token' => $token,
        ], 'index');
    }

    private function editingSelect()
    {
        $editingSession = 'show';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        if (isset($_SESSION['editing_company_id'])) {
            unset($_SESSION['editing_company_id']);
        }
        if (isset($_SESSION['editing_company_name'])) {
            unset($_SESSION['editing_company_name']);
        }

        if (isset($_POST['company_name'])) {
            $postCompany = [
                'company_name' => trim($_POST['company_name']),
            ];
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompanies = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompanies->fetchCompany();

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($postCompany['company_name'], '@')) {
            $editingCompany['company_id'] = (int)strstr($postCompany['company_name'], '@', true);
            $editingCompany['name'] = substr(strstr($postCompany['company_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->companyValidate($editingCompany, $listCompanies, 'select');
        } else {
            $errors['editing']['company_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $editingCompany['company_name'] = $editingCompany['name'];
            $_SESSION['editing_company_id'] = $editingCompany['company_id'];
            $_SESSION['editing_company_name'] = $editingCompany['name'];
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        } else {
            $editingCompany = [];
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editingCompany' => $editingCompany,
            'errors' => $errors,
        ];
    }

    private function editingUpdate()
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        if (isset($_SESSION['editing_company_id'])) {
            $editingCompany['company_id'] = $_SESSION['editing_company_id'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['company_name'])) {
            $editingCompany['company_name'] = trim($_POST['company_name']);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompanies = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompanies->fetchCompany();

        $errors['editing'] = $errors['editing'] + $this->validate->companyValidate($editingCompany, $listCompanies, 'update');

        if (!count($errors['editing'])) {
            $sqlCompanies->update($editingCompany);
            $editingCompany = [];
            if (isset($_SESSION['editing_company_id'])) {
                unset($_SESSION['editing_company_id']);
            }
            if (isset($_SESSION['editing_company_name'])) {
                unset($_SESSION['editing_company_name']);
            }
        } else {
            $editingCompany['name'] = $_SESSION['editing_company_name'];
            $editingSession = 'show';
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        }
        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editingCompany' => $editingCompany,
            'listUsers' => $listCompanies,
            'errors' => $errors,
        ];
    }

    private function editingDelete()
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        if (isset($_SESSION['editing_company_id'])) {
            unset($_SESSION['editing_company_id']);
        }
        if (isset($_SESSION['editing_company_name'])) {
            unset($_SESSION['editing_company_name']);
        }

        if (isset($_POST['company_name'])) {
            $postCompany = [
                'company_name' => trim($_POST['company_name']),
            ];
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlCompanies = $this->databaseManager->get('Company', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listCompanies = $sqlCompanies->fetchCompany();

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($postCompany['company_name'], '@')) {
            $editingCompany['company_id'] = (int)strstr($postCompany['company_name'], '@', true);
            $editingCompany['company_name'] = substr(strstr($postCompany['company_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->companyValidate($editingCompany, $listCompanies, 'delete');
        } else {
            $errors['editing']['company_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            // 選択企業の社員データの削除
            $companyId = $editingCompany['company_id'];
            $checkCompanyUsers = $companyId . '%';
            $sqlUsers = $this->databaseManager->get('User', $this->mainDatabase);
            $sqlUsers->deleteUsers($checkCompanyUsers);

            // 企業テーブルから選択企業のデータを削除
            $sqlCompanies->delete($companyId);

            // 選択企業のデータベースを削除
            $databaseCompanyId = "`" . $companyId . "`";
            $sqlCompanies->dropDatabase($databaseCompanyId);

            $editingCompany = [];
        } else {
            $editingSession = 'show';
            $editingFieldset = 'disabled';
            $selectFieldset = '';
            $editingCompany = [];
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editingCompany' => $editingCompany,
            'errors' => $errors,
        ];
    }

    private function editingElse()
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        $editingCompany = [];
        if (isset($_SESSION['company_id'])) {
            unset($_SESSION['company_id']);
        }
        if (isset($_SESSION['company_name'])) {
            unset($_SESSION['company_name']);
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'editingCompany' => $editingCompany,
            'errors' => $errors,
        ];
    }
}
