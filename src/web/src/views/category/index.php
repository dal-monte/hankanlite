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

<div class="d-flex bg-dark p-3 sticky-top">
    <div class="flex-grow-1">
        <h2 class="text-start text-white">商品カテゴリー登録・編集</h2>
    </div>
    <div class="row  d-flex flex-row-reverse">
        <div class="col">
            <a href="/menu/products" class="btn btn-light">戻る</a>
        </div>
    </div>
</div>

<div class="container mt-5" id="collapse">
    <div class="row justify-content-center">
        <div class="col-4">
            <button class="btn btn-secondary btn-lg gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#increaseCategory" aria-expanded="false" aria-controls="increaseCategory">カテゴリーの追加</button>
        </div>
        <div class="col-4">
            <button class="btn btn-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#editingCategory" aria-expanded="false" aria-controls="editingCategory">カテゴリーの編集・削除</button>
        </div>
        <div class="collapse <?php if (isset($increase)) : echo $increase;
                                endif; ?>" id="increaseCategory" data-bs-parent="#collapse">
            <div class="card card-body">
                <h3>新規商品カテゴリーの登録</h3>
                <?php if (isset($errors['increase'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['increase'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/category/increase" method="post">
                    <div class="row mt-3">
                        <label for="category_name" class="col-form-label">商品カテゴリー名</label>
                        <div class="col-auto">
                            <input value="<?php if (isset($category['category_name'])) : echo escape($category['category_name']);
                                            endif; ?>" type="text" id="category_name" name="category_name" class="form-control" aria-labelledby="nameCaution">
                        </div>
                        <div class=" col-auto">
                            <span id="nameCaution" class="form-text">
                                カテゴリー名は15文字以内で入力して下さい。
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
" id="editingCategory" data-bs-parent="#collapse">
            <div class="card card-body">
                <h3>既存商品カテゴリーの編集・削除</h3>
                <?php if (isset($errors['editing'])) : ?>
                    <ul class="text-danger">
                        <?php foreach ($errors['editing'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="/category/editing" method="post" name="editing">
                    <fieldset <?php if (isset($selectFieldset)) : echo $selectFieldset;
                                endif; ?>>
                        <div class="row mt-3">
                            <label for="category_name" class="col-form-label">商品カテゴリー名</label>
                            <div class="col-auto">
                                <select class="form-select" aria-label="Default select example" id="category_name" name="category_name">
                                    <option <?php if (isset($editingCategory['category_id'])) : echo 'value=' . escape($editingCategory['category_id']) . "@" . escape($editingCategory['category_name']);
                                            ?> selected>
                                    <?php echo escape($editingCategory['category_name']);
                                            else : echo 'selected>' . 'カテゴリー名を選択して下さい';
                                            endif; ?></option>
                                    <?php if (isset($categories)) : foreach ($categories as $category) : ?>
                                            <option value="<?php echo escape($category['category_id']) . '@' . escape($category['category_name']); ?>"><?php echo escape($category['category_name']); ?></option>
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
                                <button class="btn btn-danger" id="deleteCategory">削除</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <form action="/category/editing" method="post">
                    <fieldset <?php if (isset($editingFieldset)) : echo $editingFieldset;
                                else : echo 'disabled';
                                endif; ?>>
                        <div class="row mt-5">
                            <label for="category_name" class="col-form-label">新規商品カテゴリー名</label>
                            <div class="col-auto">
                                <input value="<?php if (isset($editingCategory['category_name'])) : echo escape($editingCategory['category_name']);
                                                endif; ?>" type="text" id="category_name" name="category_name" class="form-control" aria-labelledby="nameCaution">
                            </div>
                            <div class="col-auto">
                                <span id="nameCaution" class="form-text">
                                    カテゴリー名は15文字以内で入力して下さい。
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
    <h3 class="d-flex justify-content-center">カテゴリー 一覧</h3>
    <table class="table table-striped" id="category" style="width: 100%;">
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.5/sc-2.4.1/sp-2.3.1/datatables.min.js"></script>
<script src="/src/assets/js/category.js"></script>
<script>
    var deleteCategory = document.getElementById('deleteCategory');

    deleteCategory.addEventListener('click', function() {
        if (window.confirm('カテゴリーをテーブルから削除しますか？')) {
            deleteCategory.setAttribute('name', 'delete');
            document.editing.submit();
            return;
        } else {
            alert('キャンセルされました');
            return;
        }
    })
</script>
