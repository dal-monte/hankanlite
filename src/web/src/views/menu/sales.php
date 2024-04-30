<?php
//XSS対策
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$body = 'class="bg-success-subtle"';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/menu/products">販売管理</a>
        <div class="col d-lg-none text-nowrap">
            <a href="/login" class="btn btn-danger">ログアウト</a>
        </div>
        <form class="row g-2">
            <div class="col-4">
                <div class="form-floating d-lg-none">
                    <input type="shainbangou" class="form-control" id="floatingInputGrid" placeholder="shainbangou" value="<?php echo escape($userId); ?>">
                    <label for="floatingInputGrid">社員番号</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating d-lg-none">
                    <input type="name" class="form-control" id="floatingInputGrid2" placeholder="name" value="<?php echo escape($userName); ?>">
                    <label for="floatingInputGrid2">入力者</label>
                </div>
            </div>
        </form>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav">
                <?php if (!in_array("products", $navbar)) : echo "<!--";
                endif; ?>
                <li class="nav-item">
                    <a <?php if (isset($productsNow)) : echo $productsNow;
                        else : echo $noPage;
                        endif; ?> href="/menu/products">商品管理</a>
                </li><?php if (!in_array("products", $navbar)) : echo "-->";
                        endif; ?>

                <?php if (!in_array("sales", $navbar)) : echo "<!--";
                endif; ?>
                <li class="nav-item">
                    <a <?php if (isset($salesNow)) : echo $salesNow;
                        else : echo $noPage;
                        endif; ?> href="/menu/sales">販売管理</a>
                </li><?php if (!in_array("sales", $navbar)) : echo "-->";
                        endif; ?>

                <?php if (!in_array("purchases", $navbar)) : echo "<!--";
                endif; ?>
                <li class="nav-item">
                    <a <?php if (isset($purchasesNow)) : echo $purchasesNow;
                        else : echo $noPage;
                        endif; ?> href=" /menu/purchases">仕入管理</a>
                </li><?php if (!in_array("purchases", $navbar)) : echo "-->";
                        endif; ?>

                <?php if (!in_array("users", $navbar)) : echo "<!--";
                endif; ?>
                <li class="nav-item">
                    <a <?php if (isset($usersNow)) : echo $usersNow;
                        else : echo $noPage;
                        endif; ?> href="/menu/users">保守</a>
                </li><?php if (!in_array("users", $navbar)) : echo "-->";
                        endif; ?>
            </ul>
        </div>

    </div>
    <div class="container d-none d-lg-block">
        <div class="row g-2">
            <div class="col">
                <div class="form-floating">
                    <input type="shainbangou" class="form-control" id="floatingInputGrid3" placeholder="shainbangou" value="<?php echo $userId; ?>">
                    <label for="floatingInputGrid3">社員番号</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="name" class="form-control" id="floatingInputGrid4" placeholder="name" value="<?php echo $userName; ?>">
                    <label for="floatingInputGrid4">入力者</label>
                </div>
            </div>
            <div class="col">
                <a href="/login" class="btn btn-danger">ログアウト</a>
            </div>
        </div>
</nav>
<div class="container d-flex">
    <div class="row mt-3 g-3">
        <div class="col-auto">
            <a href="/customer" class="btn btn-secondary btn-lg">販売先顧客登録・編集</a>
        </div>
        <div class="col-auto">
            <a href="/salesContract" class="btn btn-secondary btn-lg">販売契約登録・編集</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
