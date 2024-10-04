<?php

class LoginController extends Controller
{
    protected $noPage = 'class="nav-link"';
    protected $currentPage = 'class="nav-link active" aria-current="page"';

    public function index()
    {
        session_start();
        $_SESSION = [];
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
        $token = null;

        // ルートユーザーログイン時のために登録企業情報用の変数を用意
        $listCompanies = null;

        if (!isset($_POST['user_id'])) {
            throw new HttpNotFoundException();
        }

        if (!isset($_POST['password'])) {
            throw new HttpNotFoundException();
        }

        if (isset($_POST['user_id']) && isset($_POST['password'])) {
        $user = ["user_id" => (int)trim($_POST['user_id']), "password" => trim($_POST['password'])];
        } else {
            throw new HttpNotFoundException();
        }

        // modelディレクトリのUserクラスをnewして$userに渡す
        $sqlUsers = $this->databaseManager->get('User', $this->mainDatabase);
        // POSTされたIDと同じIDがデータベースにあるか確認する
        $serverUser = $sqlUsers->check($user['user_id']);

        // IDが原因でログインに失敗した時にログイン画面に戻す
        if (is_null($serverUser)) {
            $errors['login'] = '社員番号とパスワードが一致しません';
            $view = "index";
            $serverUser["role"] = 'not';
            $serverUser["user_id"] = '';
            $serverUser["user_name"] = '';
        } else {  // 以降は入力したIDが存在する場合の処理

            // ログインに成功した後の処理を記載
            if (password_verify($user["password"], $serverUser["password"])) {

                // ログインに成功した場合の処理を記載
                session_start();
                session_regenerate_id(true); // セッションIDをふりなおす(IDを盗まれないために)

                $_SESSION["now_user_id"] = $serverUser["user_id"];
                $_SESSION["now_user_name"] = $serverUser["user_name"];
                $_SESSION["role"] = $serverUser["role"];
                $_SESSION["company_id"] = mb_substr($serverUser["user_id"], 0, 4);

                // もしログインしたのがルートユーザーならばcompanyテーブルのデータを読み込み後に登録企業の増減用の編集画面に飛ばす
                if ($serverUser["role"] === 'root') {
                    $sqlCompaniesTable = $this->databaseManager->get('Company', $this->mainDatabase);
                    $listCompanies = $sqlCompaniesTable->fetchCompany();
                    $this->convert->convertJson($listCompanies, 'company');

                    $view = "../company/index";
                } else { // ルートユーザーでなければメインメニューに飛ばす
                    $tokenAndNavbar = $this->securityCheck("products", "navbar");
                    extract($tokenAndNavbar);

                    $view = "../menu/products";
                }

            // パスワードが原因でログインに失敗した時にログイン画面に戻す
            } else {
                $errors['login'] = '社員番号とパスワードが一致しません';
                $view = "index";
                $serverUser["role"] = 'not';
                $serverUser["user_id"] = '';
                $serverUser["user_name"] = '';
            }
        }

        return $this->render([
            'title' => 'ログイン',
            'errors' => $errors,
            'noPage' => $noPage,
            'navbar' => $navbar,
            'userId' => $serverUser["user_id"],
            'userName' => $serverUser["user_name"],
            'productsNow' => $currentPage,
            'reCaptchaKey' => $reCaptchaKey,
            'companies' => $listCompanies,
            'token' => $token,
        ], $view);
    }
}
