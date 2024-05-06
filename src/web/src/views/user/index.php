<?php
//XSS対策
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$link = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css"/>
<link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.css" rel="stylesheet">
';
?>

<div class="d-flex bg-dark p-3 sticky-top">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">社員登録・編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col">
            <a href="/menu/users" class="btn btn-light">戻る</a>
        </div>
    </div>
</div>
<div class="container d-grid gap-2 d-sm-flex justify-content-sm-center">
    <div class="row mt-5" id="collapse">
        <div class="col">
            <button class="btn btn-secondary btn-lg px-4 gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample1" aria-expanded="false" aria-controls="multiCollapseExample1">新規社員登録</button>
        </div>
        <div class="col">
            <button class="btn btn-secondary btn-lg px-4" type="button" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample2" aria-expanded="false" aria-controls="multiCollapseExample2">既存社員編集</button>
        </div>
        <div class="collapse <?php if (isset($increaseSession)) : echo $increaseSession;
                                endif; ?>" id="multiCollapseExample1" data-bs-parent="#collapse">
            <div class="card card-body bg-light">
                <h3>新規社員名の登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/user/increase" method="post">
                    <div class="row mt-3">
                        <label for="user_id" class="col-form-label">社員番号</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($increaseUser['user_id'])) : echo escape($increaseUser['user_id']);
                                            endif; ?>" type="text" id="user_id" name="user_id" class="form-control" aria-labelledby="idCaution" inputmode="numeric">
                        </div>
                        <div class="col-auto">
                            <span id="idCaution" class="form-text">
                                社員番号は4桁の半角数字で入力して下さい。
                            </span>
                        </div>
                        <label for="user_name" class="col-form-label mt-3">社員名</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($increaseUser['user_id'])) : echo escape($increaseUser['user_id']);
                                            endif; ?>" type="text" id="user_name" name="user_name" class="form-control" aria-labelledby="nameCaution">
                        </div>
                        <div class="col-auto">
                            <span id="nameCaution" class="form-text">
                                社員名は30文字以内で入力して下さい。
                            </span>
                        </div>
                        <label for="role" class="col-form-label mt-3">役割</label>
                        <div class="col-auto">
                            <select class="form-select" id="role" name="role" aria-label="Default select example">
                                <option <?php if (isset($increaseUser['role_id'])) : echo 'value=' . escape($increaseUser['role_id'] . '@' . $increaseUser['role_name']); ?> selected>
                                <?php echo escape($increaseUser['role_name']);
                                        else : echo 'selected>' . '役割を選択して下さい';
                                        endif; ?></option>
                                <?php if (isset($roles)) : foreach ($roles as $role) : ?>
                                        <option value="<?php echo escape($role['role_id']) . '@' . escape($role['role_name']); ?>"><?php echo escape($role['role_name']); ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <label for="password" class="col-form-label mt-3">ログインパスワード</label>
                        <div class="col-auto position-relative">
                            <input type="password" id="password" name="password" class="form-control" aria-labelledby="passwordCaution">
                            <span id="buttonEye" class="translate-middle position-absolute top-50 end-0 bi bi-eye me-2" onclick="pushHideButton()"></span>
                        </div>
                        <div class="col-auto">
                            <span id="passwordCaution" class="form-text">
                                パスワードは半角英数でローマ字と数字を各1文字以上使用して、6文字以上の長さにして下さい。
                            </span>
                        </div>
                        <label for="password_again" class="col-form-label mt-3">ログインパスワード（確認）</label>
                        <div class="col-auto position-relative">
                            <input type="password" id="password_again" name="password_again" class="form-control" aria-labelledby="passwordAgainCaution">
                            <span id="buttonAgainEye" class="translate-middle position-absolute top-50 end-0 bi bi-eye me-2" onclick="pushHideButtonAgain()"></span>
                        </div>
                        <div class="col-auto">
                            <span id="passwordAgainCaution" class="form-text">
                                同じバスワードをもう一度入力して下さい。
                            </span>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="d-block mt-5">
                            <button type="submit" class="btn btn-success">登録</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="collapse <?php if (isset($editingSession)) : echo $editingSession;
                                endif; ?>" id="multiCollapseExample2" data-bs-parent="#collapse">
            <div class="card card-body bg-light">
                <h3>既存社員の編集・削除</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/user/editing" method="post">
                    <fieldset <?php if (isset($selectFieldset)) : echo $selectFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="user_name" class="col-form-label">社員番号 / 社員名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" name="user_name" id="user_name">
                                    <option <?php if (isset($editingUser['user_id'])) : echo 'value=' . escape($editingUser['user_id']) . '@' . escape($editingUser['user_name']); ?> selected>
                                    <?php echo escape($editingUser['user_name']);
                                            else : echo 'selected>' . '社員を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($users)) : foreach ($users as $user) : ?>
                                            <option value="<?php echo escape($user['user_id']) . '@' . escape($user['user_name']); ?>"><?php echo escape($user['user_id']) . ' / ' . escape($user['user_name']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                                            endif; ?>" type="hidden" name="token">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-secondary" name='select'>選択</button>
                            </div>
                            <div class="col-auto">
                                <button type="submit" onclick=CheckDelete() class="btn btn-danger" name='delete'>削除</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <form action="/user/editing" method="post">
                    <fieldset <?php if (isset($editingFieldset)) : echo $editingFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-5">
                            <label for="user_name" class="col-form-label mt-3">新規社員名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($editingUser['user_name'])) : echo escape($editingUser['user_name']);
                                                endif; ?>" type="text" name="user_name" id="user_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    社員名は30文字以内で入力して下さい。
                                </span>
                            </div>
                            <label for="role" class="col-form-label mt-3">役割</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" name="role" id="role">
                                    <option <?php if (isset($editingUser['role_id'])) : echo 'value=' . escape($editingUser['role_id'] . '@' . escape($editingUser['role_name'])); ?> selected>
                                    <?php echo escape($editingUser['role_name']);
                                            else : echo 'selected>' . '役割を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($roles)) : foreach ($roles as $role) : ?>
                                            <option value="<?php echo escape($role['role_id']) . '@' . escape($role['role_name']); ?>"><?php echo escape($role['role_name']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="row mt-5">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success" name='update'>編集</button>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-secondary" name='cancel'>キャンセル</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5 p-3 bg-light rounded">
    <table class="table table-striped" id="user" style="width: 100%;"></table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.js"></script>
<script src="/src/assets/js/password.js"></script>
<script src="/src/assets/js/passwordAgain.js"></script>
<script src="/src/assets/js/user.js"></script>
<script>
    function CheckDelete() {
        window.onbeforeunload = null;
        if (confirm('商品をテーブルから削除しますか？')) {
            return true;
        } else {
            alert('キャンセルされました');
            return false;
        }
    }
</script>
