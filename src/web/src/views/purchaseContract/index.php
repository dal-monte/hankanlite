<?php
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
$body = 'class="bg-warning-subtle"';
$userId = json_encode($userId);
$taxRate = json_encode($taxRate);
$link = '
<link href="https://cdn.datatables.net/v/dt/dt-1.13.10/datatables.min.css" rel="stylesheet">
';
?>

<div class="d-flex bg-dark p-3 sticky-top">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">仕入契約登録・編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col">
            <a href="/menu/purchases" class="btn btn-light">戻る</a>
        </div>
    </div>
</div>
<div class="container mt-5" id="collapse">
    <div class="row justify-content-center">
        <div class="col-4">
            <button <?php if (isset($selector)) : echo $selector;
                    endif; ?> class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#increasePurchaseContract" aria-expanded="false" aria-controls="increasePurchaseContract">新規仕入契約登録</button>
        </div>
        <div class="col-4">
            <button <?php if (isset($selector)) : echo $selector;
                    endif; ?> class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#editingPurchaseContract" aria-expanded="false" aria-controls="editingPurchaseContract">既存仕入契約編集</button>
        </div>
    </div>
    <div class="collapse <?php if (isset($increaseSession)) : echo 'show';
                            endif; ?>" id="increasePurchaseContract" data-bs-parent="#collapse">
        <div class="card card-body">
            <div class="container">
                <h3>新規仕入契約の登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/purchaseContract/increase" method="post">
                    <fieldset <?php if (isset($increaseFieldset)) : echo $increaseFieldset;
                                endif; ?>>
                        <div class="row">
                            <label for="supplier_name" class="col-form-label mt-3">ID / 業者名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="supplier_name" name="supplier_name">
                                    <option <?php if (isset($increaseContract['supplier_id'])) : echo 'value=' .  $increaseContract['supplier_id'] . '@' . escape($increaseContract['supplier_name']); ?> selected>
                                    <?php echo escape($increaseContract['supplier_name']) . ' / ' . escape($increaseContract['supplier_id']);
                                            else : echo 'selected>' . '業者を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($suppliers)) : foreach ($suppliers as $supplier) : ?>
                                            <option value="<?php echo escape($supplier['supplier_id']) . '@' . escape($supplier['supplier_name']); ?>"><?php echo escape($supplier['supplier_id']) . ' / ' . escape($supplier['supplier_name']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <label for="purchase_id" class="mt-3 col-form-label">契約番号
                            </label>
                            <div class="col-auto">
                                <input value="<?php if (isset($increaseContract['contract_id'])) : echo escape($increaseContract['contract_id']);
                                                endif; ?>" type="text" name="purchase_id" id="purchase_id" class="form-control" aria-labelledby="idCaution" inputmode="numeric">
                            </div>
                            <div class="col-auto">
                                <span id="idCaution" class="form-text">
                                    6桁の半角数字を入力して下さい
                                </span>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                                            endif; ?>" type="hidden" name="token">
                            <div class="d-block mt-5">
                                <button type="submit" class="btn btn-success">決定</button>
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
                            endif; ?>" id="editingPurchaseContract" data-bs-parent="#collapse">
        <div class="card card-body">
            <div class="container">
                <h3>既存仕入契約の編集</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/purchaseContract/editing" method="post">
                    <fieldset <?php if (isset($editingSearchFieldset)) : echo $editingSearchFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="supplier_name" class="col-form-label">業者名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="supplier_name" name="supplier_name">
                                    <option <?php if (isset($editingContract['supplier_id'])) : echo  'value=' . escape($editingContract['supplier_id']) . '@' . escape($editingContract['supplier_name']); ?> selected>
                                    <?php echo escape($editingContract['supplier_name']) . ' / ' . escape($editingContract['supplier_id']);
                                            else : echo 'selected>' . '業者を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($suppliers)) : foreach ($suppliers as $supplier) : ?>
                                            <option value="<?php echo escape($supplier['supplier_id']) . '@' . escape($supplier['supplier_name']); ?>"><?php echo escape($supplier['supplier_id']) . ' / ' . escape($supplier['supplier_name']); ?></option>
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
                <form action="/purchaseContract/editing" method="post">
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
                                            <option value="<?php echo escape($contract['purchase_contract_id']); ?>"><?php echo escape($contract['purchase_contract_id']); ?></option>
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
                                <button class="btn btn-danger" type="submit" name="delete" onclick=CheckDelete()>削除</button>
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
    <h3 class="d-flex justify-content-center">仕入契約 一覧</h3>
    <table class="table table-striped" id="purchaseContract" style="width: 100%;">
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
<script src="/src/assets/js/purchaseProduct.js"></script>
<script src="/src/assets/js/purchaseContract.js"></script>
<script>
    function CheckDelete() {
        window.onbeforeunload = null;
        if (confirm('契約をテーブルから削除しますか？')) {
            return true;
        } else {
            alert('キャンセルされました');
            return false;
        }
    }
</script>
