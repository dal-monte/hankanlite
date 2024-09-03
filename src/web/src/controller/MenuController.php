<?php

class MenuController extends Controller
{
    protected $noPage = 'class="nav-link"';
    protected $currentPage = 'class="nav-link active" aria-current="page"';

    public function products()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        session_start();
        $tokenAndNavbar = $this->securityCheck("products", "navbar");
        extract($tokenAndNavbar);

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["now_user_name"])) {
            $userId = $_SESSION["now_user_id"];
            $userName = $_SESSION["now_user_name"];
        } else {
            throw new HttpNotFoundException();
        }

        return $this->render([
            'title' => '商品管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar,
            'userId' => $userId,
            'userName' => $userName,
            'productsNow' => $currentPage,
            'token' => $token,
        ]);
    }

    public function sales()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        session_start();
        $tokenAndNavbar = $this->securityCheck("sales", "navbar");
        extract($tokenAndNavbar);

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["now_user_name"])) {
            $userId = $_SESSION["now_user_id"];
            $userName = $_SESSION["now_user_name"];
        } else {
            throw new HttpNotFoundException();
        }

        return $this->render([
            'title' => '販売管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar,
            'userId' => $userId,
            'userName' => $userName,
            'salesNow' => $currentPage,
            'token' => $token,
        ]);
    }

    public function purchases()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        session_start();
        $tokenAndNavbar = $this->securityCheck("purchases", "navbar");
        extract($tokenAndNavbar);

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["now_user_name"])) {
            $userId = $_SESSION["now_user_id"];
            $userName = $_SESSION["now_user_name"];
        } else {
            throw new HttpNotFoundException();
        }

        return $this->render([
            'title' => '仕入管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar,
            'userId' => $userId,
            'userName' => $userName,
            'purchasesNow' => $currentPage,
            'token' => $token,
        ]);
    }

    public function users()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        session_start();
        $tokenAndNavbar = $this->securityCheck("users", "navbar");
        extract($tokenAndNavbar);

        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        }
        if (isset($_SESSION["now_user_name"])) {
            $userName = $_SESSION["now_user_name"];
        }

        return $this->render([
            'title' => '社員管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar,
            'userId' => $userId,
            'userName' => $userName,
            'usersNow' => $currentPage,
            'token' => $token,
        ]);
    }
}
