<?php
//XSS対策
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$body = 'class="bg-primary-subtle"';
$link = '
<link href="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.css" rel="stylesheet">
';
?>

<div class="d-flex bg-dark p-3 sticky-top align-items-center">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">商品登録・ 編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col">
            <a href="/menu/products" class="btn btn-light">戻る</a>
        </div>
    </div>
</div>

<div class="container d-grid mt-5" id="collapse">
    <div class="row justify-content-center">
        <div class="col-4">
            <button <?php if (isset($selector)) : echo $selector;
                    endif; ?> class="btn btn-secondary btn-lg gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#increaseProduct" aria-expanded="false" aria-controls="increaseProduct">商品の追加</button>
        </div>
        <div class="col-4">
            <button <?php if (isset($selector)) : echo $selector;
                    endif; ?> class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#editingProduct" aria-expanded="false" aria-controls="editingProduct">商品の編集・削除</button>
        </div>
        <div class="collapse <?php if (isset($increase)) : echo $increase;
                                endif; ?>" id="increaseProduct" data-bs-parent="#collapse">
            <div class="card card-body">
                <h3>新規商品の登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/product/increase" method="post">
                    <fieldset>
                        <div class="row mt-3">
                            <label for="category_name" class="col-form-label mt-3">カテゴリー名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" name="category_name" id="category_name">
                                    <option <?php if (isset($increaseProduct['category_id'])) : echo 'value=' . escape($increaseProduct['category_id']) . '@' .  escape($increaseProduct['category_name']); ?> selected>
                                    <?php echo escape($increaseProduct['category_name']);
                                            else : echo 'selected>' . 'カテゴリー名を選択して下さい';
                                            endif; ?></option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?php echo escape($category['category_id']) . '@' . escape($category['category_name']); ?>"><?php echo escape($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <label for="product_name" class="col-form-label mt-3">商品名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($increaseProduct['product_name'])) : echo escape($increaseProduct['product_name']);
                                                endif; ?>" type="text" id="product_name" name="product_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    30文字以内で入力して下さい
                                </span>
                            </div>
                            <label for="increase_price" class="col-form-label mt-3">定価(税抜)</label>
                            <div class="col-auto">
                                <div class="input-group">
                                    <input value="<?php if (isset($increaseProduct['list_price'])) : echo escape($increaseProduct['list_price']);
                                                    endif; ?>" type="text" id="increase_price" name="increase_price" class="form-control text-end" aria-labelledby="yen priceCaution" inputmode="numeric" onblur="increasePrice(this)">
                                    <span class="input-group-text" id="yen">円</span>
                                    <span id="priceCaution" class="form-text ms-2">
                                        1京円以上は非対応（カンマは自動挿入）
                                    </span>
                                </div>
                            </div>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="-flex d-md-inline-flex mt-4">
                            <div class="me-auto">
                                <button type="submit" class="btn btn-success">登録</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class="collapse <?php if (isset($editing)) : echo $editing;
                                endif; ?>" id="editingProduct" data-bs-parent="#collapse">
            <div class="card card-body">
                <h3>既存商品の編集・削除</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>


                <form action="/product/editing" method="post">
                    <fieldset <?php if (isset($searchFieldset)) : echo $searchFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="category_name" class="col-form-label">カテゴリー</label>
                            <div class="col-auto">
                                <select class="form-select" name="category_name" id="category_name" aria-label="Default select example" aria-labelledby="productCaution">
                                    <option <?php if (isset($editingProduct['category_id'])) : echo 'value=' . escape($editingProduct['category_id']) . '@' . escape($editingProduct['category_name']);
                                            ?> selected>
                                    <?php echo escape($editingProduct['category_name']);
                                            else : echo 'selected>' . 'カテゴリーを選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($usedCategories)) : foreach ($usedCategories as $category) : ?>
                                            <option value="<?php echo escape($category['category_id']) . '@' . escape($category['category_name']); ?>"><?php echo escape($category['category_name']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <input value="<?php if (isset($token)) : echo $token;
                                            endif; ?>" type="hidden" name="token">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-secondary" name="search">検索</button>
                            </div>
                            <div class="col-auto">
                                <span id="productCaution" class="form-text">
                                    直接商品を選択するか、カテゴリーから商品を検索して下さい
                                </span>
                            </div>
                        </div>
                    </fieldset>
                </form>


                <form action="/product/editing" method="post">
                    <fieldset <?php if (isset($selectFieldset)) : echo $selectFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="product_name" class="col-form-label">商品</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="product_name" name="product_name">
                                    <option <?php if (isset($editingProduct['product_id'])) : echo 'value=' . escape($editingProduct['product_id']) . "@" . escape($editingProduct['product_name']); ?> selected>
                                    <?php echo escape($editingProduct['product_name']);
                                            else : echo 'selected>' . '商品を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($products)) : foreach ($products as $product) : ?>
                                            <option value="<?php echo escape($product['product_id']) . '@' . escape($product['product_name']); ?>"><?php echo escape($product['product_name']); ?></option>
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
                                <button class="btn btn-danger" id="deleteProduct">削除</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <form action="/product/editing" method="post">
                    <fieldset <?php if (isset($editingFieldset)) : echo $editingFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="category_name" class="col-form-label mt-3">新規カテゴリー</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="category_name" name="category_name">
                                    <option <?php if (isset($editingProduct['category_id'])) : echo 'value=' . $editingProduct['category_id'] . '@' . escape($editingProduct['category_name']);
                                            ?> selected>
                                    <?php echo escape($editingProduct['category_name']);
                                            else : echo 'selected>' . '';
                                            endif; ?></option>
                                    <?php if (isset($categories)) : foreach ($categories as $category) : ?>
                                            <option value="<?php echo escape($category['category_id']) . '@' . escape($category['category_name']); ?>"><?php echo escape($category['category_name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <label for="product_name" class="col-form-label mt-3">新規商品名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($editingProduct['product_name'])) : echo escape($editingProduct['product_name']);
                                                endif; ?>" type="text" id="product_name" name="product_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    30文字以内で入力して下さい
                                </span>
                            </div>
                            <label for="editing_price" class="col-form-label mt-3">定価</label>
                            <div class="col-auto">
                                <div class="input-group">
                                    <input value="<?php if (isset($editingProduct['price'])) : echo escape($editingProduct['price']);
                                                    elseif (isset($editingProduct['list_price'])) : echo escape($editingProduct['list_price']);
                                                    endif; ?>" type="text" id="editing_price" name="editing_price" class="form-control text-end" aria-labelledby="yen priceCaution" inputmode="numeric" onblur="editingPrice(this)">
                                    <span class="input-group-text" id="yen">円</span>
                                    <span id="nameCaution" class="form-text ms-2">
                                        1京円以上は非対応（カンマは自動挿入）
                                    </span>
                                </div>
                            </div>
                        </div>
                        <input value="<?php if (isset($token)) : echo $token;
                                        endif; ?>" type="hidden" name="token">
                        <div class="d-flex d-md-inline-flex mt-4">
                            <div class="me-auto">
                                <button type="submit" class="btn btn-success" name="update">編集</button>
                            </div>
                            <div class="ms-md-5">
                                <button type="submit" class="btn btn-secondary" name="cancel">キャンセル</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5 p-3 bg-light rounded">
    <table class="table table-striped" id="product" style="width: 100%;"></table>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.js"></script>
<script src="/src/assets/js/product.js"></script>
<script src="/src/assets/js/increasePrice.js"></script>
<script src="/src/assets/js/editingPrice.js"></script>
<script>
    var deleteProduct = document.getElementById('deleteProduct');

    deleteProduct.addEventListener('click', function() {
        if (window.confirm('商品をテーブルから削除しますか？')) {
            deleteProduct.setAttribute('name', 'delete');
            document.editing.submit();
            return;
        } else {
            alert('キャンセルされました');
            return;
        }
    })
</script>
