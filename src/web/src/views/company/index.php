<?php
//XSS対策
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$css = '
<style type="text/css">
.input-group {
  width: 120%;
  }
}
</style>';
$link = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css"/>
<link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.css" rel="stylesheet">
';
?>

<div class="d-flex bg-dark p-3 sticky-top">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">企業登録・編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col text-nowrap">
            <a href="/login" class="btn btn-danger">ログアウト</a>
        </div>
    </div>
</div>

<div class="container mt-5" id="collapse">
    <div class="row justify-content-center">
        <div class="col-4">
            <button class="btn btn-secondary btn-lg px-4 gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#increaseCompany" aria-expanded="false" aria-controls="increaseCompany">新規企業登録</button>
        </div>
        <div class="col-4">
            <button class="btn btn-secondary btn-lg px-4" type="button" data-bs-toggle="collapse" data-bs-target="#editingCompany" aria-expanded="false" aria-controls="editingCompany">既存企業編集</button>
        </div>
        <div class="collapse <?php if (isset($increaseSession)) : echo $increaseSession;
                                endif; ?>" id="increaseCompany" data-bs-parent="#collapse">
            <div class="card card-body bg-light">
                <h3>新規企業名の登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/company/increase" method="post">
                    <fieldset <?php if (isset($createFieldset)) : echo $createFieldset;
                            endif; ?>>
                        <div class="row mt-3">
                            <label for="company_id" class="col-form-label">企業番号</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($increaseCompany['company_id'])) : echo escape($increaseCompany['company_id']);
                                                endif; ?>" type="text" id="company_id" name="company_id" class="form-control" aria-labelledby="idCaution" inputmode="numeric">
                            </div>
                            <div class="col-auto">
                                <span id="idCaution" class="form-text">
                                    企業番号は4桁の半角数字で入力して下さい。
                                </span>
                            </div>
                            <label for="company_name" class="col-form-label mt-3">企業名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($increaseCompany['company_name'])) : echo escape($increaseCompany['company_name']);
                                                endif; ?>" type="text" id="company_name" name="company_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    企業名は30文字以内で入力して下さい。
                                </span>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                            endif; ?>" type="hidden" name="token">
                            <div class="d-block mt-5">
                                <button type="submit" class="btn btn-secondary" name="create" onclick="window.onbeforeunload=null">データベース作成</button>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <form action="/company/increase" method="post">
                    <fieldset <?php if (isset($increaseFieldset)) : echo $increaseFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="user_id" class="col-form-label mt-5">システム管理社員番号</label>
                            <div class="col-auto">
                                <div class="input-group">
                                    <span class="input-group-text"><?php if (isset($increaseCompany['company_id'])) : echo escape($increaseCompany['company_id']); else : echo '企業番号';
                                                    endif; ?></span>
                                    <input value="<?php if (isset($increaseUser['user_id'])) : echo escape($increaseUser['user_id']);
                                                    endif; ?>" type="text" id="user_id" name="user_id" class="input-group-text" aria-labelledby="idCaution" inputmode="numeric">
                                </div>
                            </div>
                            <div class="col-auto">
                                <span id="idCaution" class="form-text">
                                    社員番号は「企業番号4桁 + 4桁の半角数字」の8桁です。 下4桁を入力して下さい。
                                </span>
                            </div>
                            <label for="user_name" class="col-form-label mt-3">システム管理社員名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($increaseUser['user_name'])) : echo escape($increaseUser['user_name']);
                                                endif; ?>" type="text" id="user_name" name="user_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    システム管理社員名は30文字以内で入力して下さい。
                                </span>
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
                                <button type="submit" class="btn btn-success" name="increase" onclick="window.onbeforeunload=null">登録</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class="collapse <?php if (isset($editingSession)) : echo $editingSession;
                                endif; ?>" id="editingCompany" data-bs-parent="#collapse">
            <div class="card card-body bg-light">
                <h3>既存企業の編集・削除</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/company/editing" method="post">
                    <fieldset <?php if (isset($selectFieldset)) : echo $selectFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="company_name" class="col-form-label">企業番号 / 企業名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" name="company_name" id="company_name">
                                    <option <?php if (isset($editingCompany['company_id'])) : echo 'value=' . escape($editingCompany['company_id']) . '@' . escape($editingCompany['company_name']); ?> selected>
                                    <?php echo escape($editingCompany['company_name']);
                                            else : echo 'selected>' . '企業を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($companies)) : foreach ($companies as $company) : ?>
                                            <option value="<?php echo escape($company['company_id']) . '@' . escape($company['company_name']); ?>"><?php echo escape($company['company_id']) . ' / ' . escape($company['company_name']); ?></option>
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
                                <button class="btn btn-danger" id="deleteCompany">削除</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <form action="/company/editing" method="post">
                    <fieldset <?php if (isset($editingFieldset)) : echo $editingFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-5">
                            <label for="company_name" class="col-form-label mt-3">新規企業名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($editingCompany['company_name'])) : echo escape($editingCompany['company_name']);
                                                endif; ?>" type="text" name="company_name" id="company_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    企業名は30文字以内で入力して下さい。
                                </span>
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
    <table class="table table-striped" id="company" style="width: 100%;"></table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.js"></script>
<script src="/src/assets/js/password.js"></script>
<script src="/src/assets/js/passwordAgain.js"></script>
<script src="/src/assets/js/company.js"></script>
<script>
    var deleteCompany = document.getElementById('deleteCompany');

    deleteCompany.addEventListener('click', function() {
        if (window.confirm('企業情報をテーブルから削除しますか？')) {
            deleteCompany.setAttribute('name', 'delete');
            document.editing.submit();
            return;
        } else {
            alert('キャンセルされました');
            return;
        }
    })
</script>
