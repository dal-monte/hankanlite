<?php

class UserController extends Controller
{
    public function index()
    {
        session_start();

        $token = $this->securityCheck("users");

        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        if (isset($_SESSION['user_name'])) {
            unset($_SESSION['user_name']);
        }

        if (isset($_SESSION['now_user_id'])) {
        $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlUsers = $this->databaseManager->get('User', $this->mainDatabase);

        $searchUsers = $companyId . '%';

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $listUsers = $sqlUsers->fetchUser($searchUsers);
        // 表にデータを挿入
        $this->convert->convertJson($listUsers, 'user');

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlRoles = $this->databaseManager->get('Role', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listRoles = $sqlRoles->fetchRole();

        return $this->render([
            'title' => '社員の登録',
            'errors' => [],
            'users' => $listUsers,
            'roles' => $listRoles,
            'token' => $token,
            'companyId' => $companyId,
        ]);
    }

    public function increase()
    {
        session_start();

        $token = $this->securityCheck("users");

        $errors['increase'] = [];


        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        if (isset($_SESSION['user_name'])) {
            unset($_SESSION['user_name']);
        }

        $increaseSession = 'show';

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlUsers = $this->databaseManager->get('User', $this->mainDatabase);

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlRoles = $this->databaseManager->get('Role', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listRoles = $sqlRoles->fetchRole();

        if (isset($_POST['user_id']) && isset($_POST['user_name'])) {
            $user = [
                'user_id' => trim($_POST['user_id']),
                'user_name' => trim($_POST['user_name']),
            ];
        } else {
            throw new HttpNotFoundException();
        }

        if (!isset($_POST['role'])) {
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

        $user['user_id_before'] = $user['user_id'];

        if (isset($_SESSION['now_user_id'])) {
        $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        $user['user_id'] = $companyId . $user['user_id_before'];
        $checkCompanyUsers = $companyId . '%';
        $listUsers = $sqlUsers->fetchUser($checkCompanyUsers);
        $errors['increase'] = $errors['increase'] + $this->validate->userValidate($user, $listUsers, 'increase');

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($_POST['role'], '@')) {
            $user['role_id'] = strstr($_POST['role'], '@', true);
            $user['role_name'] = substr(strstr($_POST['role'], '@', false), 1);
            $errors['increase'] = $errors['increase'] + $this->validate->roleValidate($user, $listRoles);
        } else {
            $errors['increase']['role'] = '役割は選択肢から選んでください';
        }

        if (!count($errors['increase'])) {
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

        // カテゴリー選択フォーム用データ取り出し
        $listUsers = $sqlUsers->fetchUser($checkCompanyUsers);
        // 表にデータを挿入
        $this->convert->convertJson($listUsers, 'user');

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'users' => $listUsers,
            'roles' => $listRoles,
            'increaseUser' => $user,
            'increaseSession' => $increaseSession,
            'token' => $token,
            'companyId' => $companyId,
        ], 'index');
    }

    public function editing()
    {
        session_start();

        $token = $this->securityCheck("users");

        if (isset($_POST['user_name'])) {
            $postUser['user_name'] = trim($_POST['user_name']);
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_SESSION['now_user_id'])) {
        $companyId = mb_substr($_SESSION['now_user_id'], 0, 4);
        } else {
            throw new HttpNotFoundException();
        }

        $checkCompanyUsers = $companyId . '%';

        // modelsディレクトリのUserクラスをnewして$sqlUserに渡す
        $sqlUsers = $this->databaseManager->get('User', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listUsers = $sqlUsers->fetchUser($checkCompanyUsers);

        // modelsディレクトリの対象クラスをnewして変数に渡す
        $sqlRoles = $this->databaseManager->get('Role', $this->mainDatabase);
        // カテゴリー選択フォーム用データ取り出し
        $listRoles = $sqlRoles->fetchRole();

        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';

        if (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($postUser, $listUsers);
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate($postUser, $listRoles, $listUsers, $sqlUsers);
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($postUser, $listUsers, $sqlUsers);
            extract($editingDelete);
        } else {
            $editingElse = $this->editingElse();
            extract($editingElse);
        }

        // カテゴリー選択フォーム用データ取り出し
        $listUsers = $sqlUsers->fetchUser($checkCompanyUsers);
        // 表にデータを挿入
        $this->convert->convertJson($listUsers, 'user');

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'editingUser' => $user,
            'editingSession' => $editingSession,
            'users' => $listUsers,
            'roles' => $listRoles,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'token' => $token,
            'companyId' => $companyId,
        ], 'index');
    }

    private function editingSelect($postUser, $listUsers)
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        $editingSession = 'show';

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($postUser['user_name'], '@')) {
            $user['user_id'] = (int)strstr($postUser['user_name'], '@', true);
            $user['name'] = substr(strstr($postUser['user_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->userValidate($user, $listUsers, 'select');
        } else {
            $errors['editing']['user_name'] = '選択肢から選んでください';
        }

        if (isset($user['user_id'])) {
            foreach ($listUsers as $listUser) {
                if ($user['user_id'] === $listUser['user_id']) {
                    $user['role_id'] = $listUser['role_id'];
                    $user['role_name'] = $listUser['role'];
                }
            }
        }

        if (!count($errors['editing'])) {
            $user['user_name'] = $user['name'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        } else {
            $user = [];
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'user' => $user,
            'errors' => $errors,
        ];
    }

    private function editingUpdate($postUsers, $listRoles, $listUsers, $sqlUsers)
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        $user['user_name'] = $postUsers['user_name'];

        if (isset($_SESSION['user_id'])) {
            $user['user_id'] = $_SESSION['user_id'];
        } else {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['role'])) {
            $postUser['role'] = $_POST['role'];
        } else {
            throw new HttpNotFoundException();
        }

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($postUser['role'], '@')) {
            $user['role_id'] = (int)strstr($postUser['role'], '@', true);
            $user['role_name'] = substr(strstr($postUser['role'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->roleValidate($user, $listRoles);
        } else {
            $errors['editing']['role'] = '役割は選択肢から選んでください';
        }

        $errors['editing'] = $errors['editing'] + $this->validate->userValidate($user, $listUsers, 'update');

        if (!count($errors['editing'])) {
            $sqlUsers->update($user);
            $user = [];
            unset($_SESSION['user_name']);
            unset($_SESSION['user_id']);
        } else {
            $user['name'] = $_SESSION['user_name'];
            $editingSession = 'show';
            $editingFieldset = '';
            $selectFieldset = 'disabled';
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'user' => $user,
            'listUsers' => $listUsers,
            'errors' => $errors,
        ];
    }

    private function editingDelete($postUser, $listUsers, $sqlUsers)
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        // POSTデータが「ID＠名前」となっているので＠マークの前後を分けて変数に入れてバリデーション
        if (strpos($postUser['user_name'], '@')) {
            $user['user_id'] = (int)(strstr($postUser['user_name'], '@', true));
            $user['name'] = substr(strstr($postUser['user_name'], '@', false), 1);
            foreach ($listUsers as $listUser) {
                if ($user['user_id'] === $listUser['user_id']) {
                    $user['role_id'] = $listUser['role_id'];
                    $user['role_name'] = $listUser['role'];
                }
            }
            $errors['editing'] = $errors['editing'] + $this->validate->userValidate($user, $listUsers, 'delete');
        } else {
            $errors['editing']['user_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $user['user_name'] = $user['name'];
            $sqlUsers->delete($user);
            $user = [];
        } else {
            $editingSession = 'show';
            $editingFieldset = 'disabled';
            $selectFieldset = '';
            $user = [];
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'user' => $user,
            'listUsers' => $listUsers,
            'errors' => $errors,
        ];
    }

    private function editingElse()
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        $user = [];
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        if (isset($_SESSION['user_name'])) {
            unset($_SESSION['user_name']);
        }

        return [
            'editingSession' => $editingSession,
            'editingFieldset' => $editingFieldset,
            'selectFieldset' => $selectFieldset,
            'user' => $user,
            'errors' => $errors,
        ];
    }
}
