<?php

$errors = [];

class LoginController extends Controller
{
    protected $noPage = 'class="nav-link"';
    protected $currentPage = 'class="nav-link active" aria-current="page"';

    public function index()
    {
        session_start();
        session_destroy();

        $reCaptchaKey = $this->reCaptcha->checkSetReCaptcha();

        return $this->render([
            'title' => 'ログイン',
            'errors' => [],
            'reCaptchaKey' => $reCaptchaKey,
        ]);
    }

    public function check()
    {
        if (!$this->request->isPost()) {
            throw new HttpNotFoundException();
        }

        $reCaptchaKey = $this->reCaptcha->checkSetReCaptcha();
        $this->judgeReCaptcha($reCaptchaKey);

        $errors = [];
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;
        $navbar = $this->navbar;
        $serverUser = [];

        // modelディレクトリのUserクラスをnewして$userに渡す
        $sqlUsers = $this->databaseManager->get('User');


        if (!isset($_POST['user_id'])) {
            throw new HttpNotFoundException();
        }

        if (!isset($_POST['password'])) {
            throw new HttpNotFoundException();
        }

        $user = ["user_id" => (int)trim($_POST['user_id']), "password" => trim($_POST['password'])];

        // POSTされたIDと同じIDがデータベースにあるか確認する
        $serverUser = $sqlUsers->check($user['user_id']);

        if (is_null($serverUser)) {
            $errors['login'] = '社員番号とパスワードが一致しません';
        } else {
            if (password_verify($user["password"], $serverUser["password"])) {
                session_start();
                session_regenerate_id(true); // セッションIDをふりなおす(IDを盗まれないために)
                $_SESSION["now_user_id"] = $serverUser["user_id"];
                $_SESSION["now_user_name"] = $serverUser["name"];
                $_SESSION["role"] = $serverUser["role"];
                $view = "../menu/products";
            } else {
                $errors['login'] = '社員番号とパスワードが一致しません';
                $view = "index";
                $serverUser["role"] = 'not';
                $serverUser["user_id"] = '';
                $serverUser["name"] = '';
            }
        }

        return $this->render([
            'title' => 'ログイン',
            'errors' => $errors,
            'noPage' => $noPage,
            'navbar' => $navbar[$serverUser["role"]],
            'userId' => $serverUser["user_id"],
            'userName' => $serverUser["name"],
            'productsNow' => $currentPage,
            'reCaptchaKey' => $reCaptchaKey,
        ], $view);
    }
}
