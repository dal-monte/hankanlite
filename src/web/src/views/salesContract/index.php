<?php
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
$body = 'class="bg-success-subtle"';
$userId = json_encode($userId);
$taxRate = json_encode($taxRate);
$link = '
<link href="https://cdn.datatables.net/v/dt/dt-1.13.10/datatables.min.css" rel="stylesheet">
';
?>

<div class="d-flex bg-dark p-3 sticky-top">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">販売契約登録・編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col">
            <a href="/menu/sales" class="btn btn-light">戻る</a>
        </div>
    </div>
</div>

<div class="container mt-5" id="collapse">
    <div class="row justify-content-center">
        <div class="col-4">
            <button <?php if (isset($selector)) : echo $selector;
                    endif; ?> class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#increaseSalesContract" aria-expanded="false" aria-controls="increaseSalesContract">新規販売契約登録</button>
        </div>
        <div class="col-4">
            <button <?php if (isset($selector)) : echo $selector;
                    endif; ?> class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#editingSalesContract" aria-expanded="false" aria-controls="editingSalesContract">既存販売契約編集</button>
        </div>
    </div>
    <div class="collapse <?php if (isset($increaseSession)) : echo 'show';
                            endif; ?>" id="increaseSalesContract" data-bs-parent="#collapse">
        <div class="card card-body">
            <div class="container">
                <h3>新規販売契約の登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/salesContract/increase" method="post">
                    <fieldset <?php if (isset($increaseFieldset)) : echo $increaseFieldset;
                                endif; ?>>
                        <div class="row">
                            <label for="customer_name" class="col-form-label mt-3">ID / 顧客名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="customer_name" name="customer_name">
                                    <option <?php if (isset($increaseContract['customer_id'])) : echo 'value=' . escape($increaseContract['customer_id']) . '@' . escape($increaseContract['customer_name']); ?> selected>
                                    <?php echo escape($increaseContract['customer_name']) . ' / ' . escape($increaseContract['customer_id']);
                                            else : echo 'selected>' . '顧客を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($customers)) : foreach ($customers as $customer) : ?>
                                            <option value="<?php echo escape($customer['customer_id']) . '@' . escape($customer['customer_name']); ?>"><?php echo escape($customer['customer_id']) . ' / ' . escape($customer['customer_name']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <label for="sales_id" class="mt-3 col-form-label">契約番号
                            </label>
                            <div class="col-auto">
                                <input value="<?php if (isset($increaseContract['contract_id'])) : echo escape($increaseContract['contract_id']);
                                                endif; ?>" type="text" name="sales_id" id="sales_id" class="form-control" aria-labelledby="idCaution" inputmode="numeric">
                            </div>
                            <div class="col-auto">
                                <span id="idCaution" class="form-text">
                                    6桁の半角数字を入力して下さい
                                </span>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                                            endif; ?>" type="hidden" name="token">
                            <div class="d-block mt-5">
                                <button type="submit" class="btn btn-success" name="create">決定</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div>
                    <div><?php if (isset($table) and isset($increaseSession)) : echo $table;
                            endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="collapse <?php if (isset($editingSession)) : echo $editingSession;
                            endif; ?>" id="editingSalesContract" data-bs-parent="#collapse">
        <div class="card card-body">
            <div class="container">
                <h3>既存販売契約の編集</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/salesContract/editing" method="post">
                    <fieldset <?php if (isset($editingSearchFieldset)) : echo $editingSearchFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="customer_name" class="col-form-label">顧客名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="customer_name" name="customer_name">
                                    <option <?php if (isset($editingContract['customer_id'])) : echo 'value=' . escape($editingContract['customer_id']) . '@' . escape($editingContract['customer_name']); ?> selected>
                                    <?php echo escape($editingContract['customer_name']) . ' / ' . escape($editingContract['customer_id']);
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
                                <button type="submit" class="btn btn-secondary" name="search">契約番号を検索</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <form action="/salesContract/editing" method="post">
                    <fieldset <?php if (isset($editingSelectFieldset)) : echo $editingSelectFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-3">
                            <label id="contract_id" class="col-form-label">契約番号</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" name="contract_id" id="contract_id">
                                    <option <?php if (isset($editingContract['contract_id'])) : echo 'value=' . escape($editingContract['contract_id']); ?> selected>
                                    <?php echo escape($editingContract['contract_id']);
                                            else : echo 'selected>' . '契約番号を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($contracts)) : foreach ($contracts as $contract) : ?>
                                            <option value="<?php echo escape($contract['sales_contract_id']); ?>"><?php echo escape($contract['sales_contract_id']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                                            endif; ?>" type="hidden" name="token">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success" name="select">編集</button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-danger" id="deleteContract">削除</button>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-secondary" name="cancel">キャンセル</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div>
                    <div><?php if (isset($table) and isset($editingSession)) : echo $table;
                            endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($table === '') : echo
    '<div class="container mt-5 p-3 bg-light rounded">
    <h3 class="d-flex justify-content-center">販売契約 一覧</h3>
    <table class="table table-striped" id="salesContract" style="width: 100%;">
    </table>
</div>
';
endif; ?>
<script>
    const userId = JSON.parse('<?php echo $userId; ?>');
    const taxRate = JSON.parse('<?php echo $taxRate; ?>');
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.10/b-2.4.2/b-html5-2.4.2/date-1.5.2/b-print-2.4.2/fh-3.4.0/r-2.5.0/sc-2.3.0/sp-2.2.0/datatables.min.js"></script>
<script src="/src/assets/js/salesProduct.js"></script>
<script src="/src/assets/js/salesContract.js"></script>
<script>
    var deleteContract = document.getElementById('deleteContract');

    deleteContract.addEventListener('click', function() {
        if (window.confirm('契約をテーブルから削除しますか？')) {
            deleteContract.setAttribute('name', 'delete');
            document.editing.submit();
            return;
        } else {
            alert('キャンセルされました');
            return;
        }
    })
</script>
