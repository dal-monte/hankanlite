<?php

$errors = [];

class UserController extends Controller
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
        if (!in_array("users", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

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
        if (!in_array("users", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

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
        if (!in_array("users", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        // modelsディレクトリのUserクラスをnewして$sqlUserに渡す
        $sqlUsers = $this->databaseManager->get('User');

        if (isset($_POST['user_name'])) {
            $user['user_name'] = trim($_POST['user_name']);
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
            $editingSession = 'show';
            if (strpos($user['user_name'], '@')) {
                $user['user_id'] = strstr($user['user_name'], '@', true);
                $user['name'] = substr(strstr($user['user_name'], '@', false), 1);
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
        } elseif (isset($_POST['update'])) {
            if (isset($_SESSION['user_id'])) {
                $user['user_id'] = $_SESSION['user_id'];
            } else {
                throw new HttpNotFoundException();
            }

            if (isset($_POST['role'])) {
                $user['role'] = $_POST['role'];
            } else {
                throw new HttpNotFoundException();
            }

            if (strpos($user['role'], '@')) {
                $user['role_id'] = strstr($user['role'], '@', true);
                $user['role_name'] = substr(strstr($user['role'], '@', false), 1);
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
                $listUser = $sqlUsers->fetchUser();
                $this->convert->convertJson($listUser, 'user');
            } else {
                $user['name'] = $_SESSION['user_name'];
                $editingSession = 'show';
                $editingFieldset = '';
                $selectFieldset = 'disabled';
            }
        } elseif (isset($_POST['delete'])) {
            if (strpos($user['user_name'], '@')) {
                $user['user_id'] = strstr($user['user_name'], '@', true);
                $user['name'] = substr(strstr($user['user_name'], '@', false), 1);
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
        } else {
            $user = [];
            if (isset($_SESSION['user_id'])) {
                unset($_SESSION['user_id']);
            }
            if (isset($_SESSION['user_name'])) {
                unset($_SESSION['user_name']);
            }
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
}
