<?php
function escapeTable($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
if (isset($negativeQuantities)) {
    $negativeQuantities = json_encode($negativeQuantities);
}
?>

<div class="row mt-5" id="collapse2">
    <div class="col-auto">
        <button <?php if (isset($selector)) : echo 'disabled';
                endif; ?> class="btn btn-secondary gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#increaseProduct" aria-expanded="false" aria-controls="increaseProduct">商品の追加
        </button>
    </div>
    <div class="col-auto">
        <button <?php if (isset($selector)) : echo 'disabled';
                endif; ?> class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editingProduct" aria-expanded="false" aria-controls="editingProduct">商品の編集・削除
        </button>
    </div>
    <div class="collapse <?php if (isset($increase)) : echo $increase;
                            endif; ?>" id="increaseProduct" data-bs-parent="#collapse2">
        <div class="card card-body">
            <h3>新規商品の登録</h3>
            <?php if (isset($errors['increaseTable'])) : ?>
                <ul class="text-danger">
                    <?php foreach ($errors['increaseTable'] as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <form action="/<?= $controllerName; ?>/<?= $actionName; ?>Table" method="post">
                <fieldset <?php if (isset($searchIncreaseFieldset)) : echo $searchIncreaseFieldset;
                            endif; ?>>
                    <div class="row mt-3">
                        <label for="category_name" class="col-form-label">カテゴリー</label>
                        <div class="col-auto">
                            <select class="form-select" name="category_name" id="category_name" aria-label="Default select example">
                                <option value="<?php if (isset($increaseProduct['category_id'])) : echo escapeTable($increaseProduct['category_id']) . '@' . escapeTable($increaseProduct['category_name']);
                                                endif; ?>" selected><?php if (isset($increaseProduct['category_name'])) : echo escapeTable($increaseProduct['category_name']);
                                                                    else : echo 'カテゴリー名を選択して下さい';
                                                                    endif; ?></option>
                                <?php if (isset($listUsedCategories)) : foreach ($listUsedCategories as $category) : ?>
                                        <option value="<?php echo escapeTable($category['category_id']) . '@' . escapeTable($category['category_name']); ?>"><?php echo escapeTable($category['category_name']); ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-secondary" name="tableIncreaseSearch" onclick="window.onbeforeunload=null">検索</button>
                        </div>
                    </div>
                </fieldset>
            </form>
            <form action="/<?= $controllerName; ?>/<?= $actionName; ?>Table" method="post">
                <fieldset <?php if (!isset($selectIncreaseFieldset)) : echo 'disabled';
                            endif; ?>>
                    <div class="row mt-3">
                        <label for="product_name" class="col-form-label">商品</label>
                        <div class="col-auto">
                            <select class="form-select" name="product_name" id="product_name" aria-label="Default select example">
                                <option <?php if (isset($increaseProduct['product_id'])) : echo 'value=' . $increaseProduct['product_id'] . '@' . escapeTable($increaseProduct['product_name']);
                                        ?> selected>
                                <?php echo escapeTable($increaseProduct['product_name']);
                                        else : echo 'selected>' . '商品を選択して下さい';
                                        endif; ?></option>
                                <?php if (isset($listProducts)) : foreach ($listProducts as $product) : ?>
                                        <option value="<?php echo escapeTable($product['product_id']) . '@' . escapeTable($product['product_name']); ?>"><?php echo escapeTable($product['product_name']); ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-secondary" name="tableIncreaseSelect" onclick="window.onbeforeunload=null">選択</button>
                        </div>
                    </div>
                </fieldset>
            </form>

            <form action="/<?= $controllerName; ?>/<?= $actionName; ?>Table" method="post">
                <fieldset <?php if (!isset($increaseFieldset)) : echo 'disabled';
                            endif; ?>>
                    <div class="row mt-3">
                        <label for="increase_price" class="col-form-label mt-3">価格(税抜)</label>
                        <div class="col-auto">
                            <div class="input-group">
                                <input value="<?php if (isset($increaseProduct['price'])) : echo escapeTable($increaseProduct['price']);
                                                endif; ?>" type="text" id="increase_price" name="increase_price" class="form-control text-end" aria-labelledby="yen priceCaution" inputmode="numeric" onblur="increasePrice(this)">
                                <span class="input-group-text" id="yen">円</span>
                                <span id="priceCaution" class="form-text ms-2">
                                    <?php if (isset($increaseProduct['list_price'])) : echo '販売定価は' . escapeTable($increaseProduct['list_price']) . '円です。';
                                    endif; ?>
                                </span>
                            </div>
                        </div>
                        <label for="text" class="col-form-label mt-3" inputmode="numeric">個数</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($increaseProduct['number'])) : echo escapeTable($increaseProduct['number']);
                                            endif; ?>" type="number" name="number" id="number" class="form-control" aria-labelledby="nameCaution">
                        </div>
                        <div class="col-auto">
                            <span id="nameCaution" class="form-text">
                                <?php if (isset($increaseProduct['quantity'])) : echo '在庫数は' . escapeTable($increaseProduct['quantity']) . '個です。';
                                endif; ?>
                            </span>
                        </div>
                    </div>
                    <input value="<?php if (isset($token)) : echo $token;
                                    endif; ?>" type="hidden" name="token">
                    <div class="d-flex d-md-inline-flex mt-4">
                        <div class="me-auto">
                            <button type="submit" class="btn btn-success" name="tableIncrease" onclick="window.onbeforeunload=null">追加</button>
                        </div>
                        <div class="ms-md-5">
                            <form action="/<?= $controllerName; ?>/create" method="post">
                                <button class="btn btn-secondary" type="submit" name="tableCancel" onclick="window.onbeforeunload=null">キャンセル</button>
                            </form>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    <div class="collapse <?php if (isset($editing)) : echo $editing;
                            endif; ?>" id="editingProduct" data-bs-parent="#collapse2">
        <div class="card card-body">
            <h3>既存商品の編集・削除</h3>
            <?php if (isset($errors['editingTable'])) : ?>
                <ul class="text-danger">
                    <?php foreach ($errors['editingTable'] as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <form action="/<?= $controllerName; ?>/<?= $actionName; ?>Table" method="post">
                <fieldset <?php if (isset($selectEditingFieldset)) : echo $selectEditingFieldset;
                            endif; ?>>
                    <div class="row mt-3">
                        <label for="product_name" class="col-form-label">商品</label>
                        <div class="col-auto">
                            <select class="form-select" name="product_name" id="product_name" aria-label="Default select example">
                                <option value="<?php if (isset($editingProduct['product_id'])) : echo $editingProduct['product_id'] . '@' . escapeTable($editingProduct['product_name']);
                                                endif; ?>" selected>
                                    <?php if (isset($editingProduct['product_name'])) : echo escapeTable($editingProduct['product_name']);
                                    else : echo '商品を選択して下さい';
                                    endif; ?></option>
                                <?php if (isset($listSelectedProducts)) : foreach ($listSelectedProducts as $listSelectedProduct) : ?>
                                        <option value="<?php echo escapeTable($listSelectedProduct['product_id']) . '@' . escapeTable($listSelectedProduct['product_name']); ?>"><?php echo escapeTable($listSelectedProduct['product_name']); ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-secondary" name="tableEditingSelect" onclick="window.onbeforeunload=null">編集</button>
                        </div>
                        <div class="col-auto me-3">
                            <button class="btn btn-danger" id="deleteTableProduct">削除</button>
                        </div>
                    </div>
                </fieldset>
            </form>
            <form action="/<?= $controllerName; ?>/<?= $actionName; ?>Table" method="post">
                <fieldset <?php if (isset($editingFieldset)) : echo $editingFieldset;
                            else : echo 'disabled';
                            endif; ?>>
                    <div class="row mt-3">
                        <label for="editing_price" class="col-form-label mt-5">価格</label>
                        <div class="col-auto">
                            <div class="input-group">
                                <input value="<?php if (isset($editingProduct['price'])) : echo escapeTable($editingProduct['price']);
                                                endif; ?>" type="text" id="editing_price" name="editing_price" class="form-control text-end" aria-labelledby="yen priceCaution" inputmode="numeric" onblur="editingPrice(this)">
                                <span class="input-group-text" id="yen">円</span>
                                <span id="priceCaution" class="form-text ms-2">
                                    <?php if (isset($editingProduct['list_price'])) : echo '販売定価は' . escapeTable($editingProduct['list_price']) . '円です。';
                                    endif; ?>
                                </span>
                            </div>
                        </div>
                        <label for="text" class="col-form-label mt-3" inputmode="numeric">個数</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($editingProduct['number'])) : echo escapeTable($editingProduct['number']);
                                            endif; ?>" type="number" name="number" id="number" class="form-control" aria-labelledby="nameCaution">
                        </div>
                        <div class="col-auto">
                            <span id="nameCaution" class="form-text">
                                <?php if (isset($editingProduct['quantity'])) : echo '在庫数は、現契約分を入れて' . escapeTable($editingProduct['quantity']) . '個です。';
                                endif; ?>
                            </span>
                        </div>
                    </div>
                    <input value="<?php if (isset($token)) : echo $token;
                                    endif; ?>" type="hidden" name="token">
                    <div class="d-flex d-md-inline-flex mt-4">
                        <div class="me-auto">
                            <button class="btn btn-success" type="submit" name="tableEditingUpdate" onclick="window.onbeforeunload=null">確定</button>
                        </div>
                        <div class="ms-md-5">
                            <button class="btn btn-secondary" type="submit" name="tableCancel" onclick="window.onbeforeunload=null">キャンセル</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

</div>

<div class="mt-1">
    <table class="table table-striped" style="width: 100%;" id="contract">
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right">税抜合計:</th>
                <th></th>
            </tr>
            <tr>
                <th colspan="4" style="text-align:right">税込合計:</th>
                <th class="plusTax"></th>
            </tr>
        </tfoot>
    </table>
    <div class="d-flex flex-row-reverse mt-5">
        <form action="/<?= $controllerName; ?>" method="post" onclick="return CheckNegative()">
            <input value="<?php if (isset($token)) : echo $token;
                            endif; ?>" type="hidden" name="token">
            <button type="submit" class="btn btn-danger btn-lg" name="tableSubmit">入力完了</button>
        </form>
    </div>
</div>

<?php if (isset($negativeQuantities)) : ?>
    <script>
        const negativeQuantities = JSON.parse('<?php echo $negativeQuantities; ?>');
    </script>
<?php endif; ?>
<script src="/src/assets/js/increasePrice.js"></script>
<script src="/src/assets/js/editingPrice.js"></script>
<script>
    // テーブル表示中にページ遷移しようとした時に確認ダイアログを表示
    window.onbeforeunload = function(e) {
        return 'このページから移動してもよろしいですか？';
    };

    // フォーム送信時に確認ダイアログを無効化
    document.querySelector('form').addEventListener('submit', function() {
        window.onbeforeunload = null;
    });
</script>
<script>
    var deleteTableProduct = document.getElementById('deleteTableProduct');

    deleteTableProduct.addEventListener('click', function() {
        if (window.confirm('商品をテーブルから削除しますか？')) {
            deleteTableProduct.setAttribute('name', 'tableEditingDelete');
            document.editing.submit();
            return;
        } else {
            alert('キャンセルされました');
            return;
        }
    })
</script>
<script>
    function CheckNegative() {
        window.onbeforeunload = null;
        if (typeof negativeQuantities != 'undefined') {
            if (confirm('下記商品の在庫数が不足していますがよろしいですか？' + '\n' + negativeQuantities.join('\n'))) {
                return true;
            } else {
                return false;
            }
        }
    }
</script>
