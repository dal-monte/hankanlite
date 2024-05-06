<?php
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$body = 'class="bg-success-subtle"';
$link = '
<link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.css" rel="stylesheet">
';
?>

<div class="d-flex bg-dark p-3 sticky-top">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">販売先顧客登録・編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col">
            <a href="/menu/sales" class="btn btn-light">戻る</a>
        </div>
    </div>
</div>

<div class="container d-grid gap-2 d-sm-flex justify-content-sm-center">
    <div class="row mt-5" id="collapse">
        <div class="col-auto">
            <button class="btn btn-secondary btn-lg gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#increaseCustomer" aria-expanded="false" aria-controls="increaseCustomer">顧客情報の追加</button>
        </div>
        <div class="col-auto">
            <button class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#editingCustomer" aria-expanded="false" aria-controls="editingCustomer">顧客情報の編集・削除</button>
        </div>
        <div class="collapse <?php if (isset($increase)) : echo $increase;
                                endif; ?>" id="increaseCustomer" data-bs-parent="#collapse">
            <div class="card card-body">
                <h3>新規顧客情報の登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/customer/increase" method="post">
                    <div class="row mt-3">
                        <label for="customer_id" class="col-form-label">顧客ID</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($customer['customer_id'])) : echo escape($customer['customer_id']);
                                            endif; ?>" type="text" id="customer_id" name="customer_id" class="form-control" aria-labelledby="idCaution">
                        </div>
                        <div class=" col-auto">
                            <span id="idCaution" class="form-text">
                                顧客識別用番号 5桁の半角数字で入力して下さい。
                            </span>
                        </div>
                        <label for="customer_name" class="col-form-label mt-3">顧客名</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($customer['customer_name'])) : echo escape($customer['customer_name']);
                                            endif; ?>" type="text" id="customer_name" name="customer_name" class="form-control" aria-labelledby="nameCaution">
                        </div>
                        <div class=" col-auto">
                            <span id="nameCaution" class="form-text">
                                顧客名は30文字以内で入力して下さい。
                            </span>
                        </div>
                    </div>
                    <input value="<?php if (isset($token)) : echo $token;
                                    endif; ?>" type="hidden" name="token">
                    <div class="d-block mt-5">
                        <button type="submit" class="btn btn-success">登録</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="collapse <?php if (isset($editing)) : echo $editing;
                                endif; ?>
" id="editingCustomer" data-bs-parent="#collapse">
            <div class="card card-body">
                <h3>既存顧客情報の編集・削除</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/customer/editing" method="post" name="editing">
                    <fieldset <?php if (isset($selectFieldset)) : echo $selectFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="customer_name" class="col-form-label">ID / 顧客名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="customer_name" name="customer_name">
                                    <option <?php if (isset($editingCustomer['customer_id'])) : echo 'value=' .  escape($editingCustomer['customer_id']) . '@' .  escape($editingCustomer['customer_name']); ?> selected>
                                    <?php echo escape($editingCustomer['customer_id']) . ' / ' .  escape($editingCustomer['customer_name']);
                                            else : echo 'selected>' . '顧客を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($customers)) : foreach ($customers as $customer) : ?>
                                            <option value="<?php echo escape($customer['customer_id']) . '@' . escape($customer['customer_name']); ?>"><?php echo escape($customer['customer_id']) . ' / ' . escape($customer['customer_name']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                                            endif; ?>" type="hidden" name="token">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-secondary" name="select">選択</button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-danger" id="deleteCustomer">削除</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <form action="/customer/editing" method="post">
                    <fieldset <?php if (isset($editingFieldset)) : echo $editingFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-5">
                            <label for="customer_name" class="col-form-label">新規顧客名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($editingCustomer['customer_name'])) : echo escape($editingCustomer['customer_name']);
                                                endif; ?>" type="text" id="customer_name" name="customer_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    顧客名は30文字以内で入力して下さい。
                                </span>
                            </div>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="d-flex d-md-inline-flex mt-4">
                            <div class="me-auto">
                                <button class="btn btn-success" type="submit" name="update">編集</button>
                            </div>
                            <div class="ms-md-5">
                                <button class="btn btn-secondary" type="submit" name="cancel">キャンセル</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5 p-3 bg-light rounded">
    <h3 class="d-flex justify-content-center">顧客 一覧</h3>
    <table class="table table-striped" id="customer" style="width: 100%;">
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.js"></script>
<script src="/src/assets/js/customer.js"></script>
<script>
    var deleteCustomer = document.getElementById('deleteCustomer');

    deleteCustomer.addEventListener('click', function() {
        if (window.confirm('顧客情報をテーブルから削除しますか？')) {
            deleteCustomer.setAttribute('name', 'delete');
            document.editing.submit();
            return;
        } else {
            alert('キャンセルされました');
            return;
        }
    })
</script>
