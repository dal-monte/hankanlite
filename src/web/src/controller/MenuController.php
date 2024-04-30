<?php

$errors = [];

class MenuController extends Controller
{
    protected $noPage = 'class="nav-link"';
    protected $currentPage = 'class="nav-link active" aria-current="page"';

    public function products()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        $navbar = $this->navbar;
        $role = "not";
        session_start();
        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }

        if (!in_array("products", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

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
            'navbar' => $navbar[$role],
            'userId' => $userId,
            'userName' => $userName,
            'productsNow' => $currentPage
        ]);
    }
    public function sales()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        $navbar = $this->navbar;
        $role = "not";
        session_start();
        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["now_user_name"])) {
            $userId = $_SESSION["now_user_id"];
            $userName = $_SESSION["now_user_name"];
        } else {
            throw new HttpNotFoundException();
        }

        if (!in_array("sales", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        return $this->render([
            'title' => '販売管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar[$role],
            'userId' => $userId,
            'userName' => $userName,
            'salesNow' => $currentPage
        ]);
    }

    public function purchases()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        $navbar = $this->navbar;
        $role = "not";
        session_start();
        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }

        if (isset($_SESSION["now_user_id"]) && isset($_SESSION["now_user_name"])) {
            $userId = $_SESSION["now_user_id"];
            $userName = $_SESSION["now_user_name"];
        } else {
            throw new HttpNotFoundException();
        }

        if (!in_array("purchases", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        return $this->render([
            'title' => '仕入管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar[$role],
            'userId' => $userId,
            'userName' => $userName,
            'purchasesNow' => $currentPage
        ]);
    }

    public function users()
    {
        $noPage = $this->noPage;
        $currentPage = $this->currentPage;

        $navbar = $this->navbar;
        $role = "not";
        session_start();
        if (isset($_SESSION["role"])) {
            $role = $_SESSION["role"];
        }
        if (isset($_SESSION["now_user_id"])) {
            $userId = $_SESSION["now_user_id"];
        }
        if (isset($_SESSION["now_user_name"])) {
            $userName = $_SESSION["now_user_name"];
        }
        if (!in_array("users", $navbar[$role])) {
            throw new HttpNotFoundException();
        }

        return $this->render([
            'title' => '社員管理',
            'errors' => [],
            'noPage' => $noPage,
            'navbar' => $navbar[$role],
            'userId' => $userId,
            'userName' => $userName,
            'usersNow' => $currentPage
        ]);
    }
}
