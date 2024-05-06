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

        $sqlUsers = $this->databaseManager->get('User');
        // // mysqlのUsersテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listUsers = $sqlUsers->fetchUser();
        $this->convert->convertJson($listUsers, 'user');

        $sqlRoles = $this->databaseManager->get('Role');
        $listRoles = $sqlRoles->fetchRole();

        return $this->render([
            'title' => '社員の登録',
            'errors' => [],
            'users' => $listUsers,
            'roles' => $listRoles,
            'token' => $token,
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

        // modelsディレクトリのUserクラスをnewして$sqlUserに渡す
        $sqlUsers = $this->databaseManager->get('User');

        $sqlRoles = $this->databaseManager->get('Role');
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

        $listUsers = $sqlUsers->fetchUser();
        $errors['increase'] = $errors['increase'] + $this->validate->userValidate($user, $listUsers, 'increase');

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
            $listUsers = $sqlUsers->fetchUser();
            $this->convert->convertJson($listUsers, 'user');
        } else {
            $user['password'] = '';
            $user['password_again'] = '';
        }

        return $this->render([
            'title' => '社員の登録',
            'errors' => $errors,
            'users' => $listUsers,
            'roles' => $listRoles,
            'increaseUser' => $user,
            'increaseSession' => $increaseSession,
            'token' => $token,
        ], 'index');
    }

    public function editing()
    {
        session_start();

        $token = $this->securityCheck("users");

        // modelsディレクトリのUserクラスをnewして$sqlUserに渡す
        $sqlUsers = $this->databaseManager->get('User');

        if (isset($_POST['user_name'])) {
            $postUser['user_name'] = trim($_POST['user_name']);
        } else {
            throw new HttpNotFoundException();
        }

        // // mysqlのcategoriesテーブルの内容をfetch_allしてjsonファイルにエクスポート
        $listUsers = $sqlUsers->fetchUser();
        $this->convert->convertJson($listUsers, 'user');

        $sqlRoles = $this->databaseManager->get('Role');
        $listRoles = $sqlRoles->fetchRole();

        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        if (isset($_POST['select'])) {
            $editingSelect = $this->editingSelect($postUser, $listUsers);
            extract($editingSelect);
        } elseif (isset($_POST['update'])) {
            $editingUpdate = $this->editingUpdate($sqlUsers, $listRoles, $listUsers);
            extract($editingUpdate);
        } elseif (isset($_POST['delete'])) {
            $editingDelete = $this->editingDelete($postUser, $listUsers, $sqlUsers);
            extract($editingDelete);
        } else {
            $editingElse = $this->editingElse();
            extract($editingElse);
        }

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
        ], 'index');
    }

    private function editingSelect($postUser, $listUsers)
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

        $editingSession = 'show';
        if (strpos($postUser['user_name'], '@')) {
            $user['user_id'] = strstr($postUser['user_name'], '@', true);
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

    private function editingUpdate($sqlUsers, $listRoles, $listUsers)
    {
        $editingSession = '';
        $editingFieldset = 'disabled';
        $selectFieldset = '';
        $errors['editing'] = [];

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

        if (strpos($postUser['role'], '@')) {
            $user['role_id'] = strstr($postUser['role'], '@', true);
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
            $listUsers = $sqlUsers->fetchUser();
            $this->convert->convertJson($listUsers, 'user');
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

        if (strpos($postUser['user_name'], '@')) {
            $user['user_id'] = strstr($postUser['user_name'], '@', true);
            $user['name'] = substr(strstr($postUser['user_name'], '@', false), 1);
            $errors['editing'] = $errors['editing'] + $this->validate->userValidate($user, $listUsers, 'delete');
        } else {
            $errors['editing']['user_name'] = '選択肢から選んでください';
        }

        if (!count($errors['editing'])) {
            $user['user_name'] = $user['name'];
            $sqlUsers->delete($user);
            $listUsers = $sqlUsers->fetchUser();
            $this->convert->convertJson($listUsers, 'user');
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
