<?php
$html = 'style="height: 100%;"';
$body = 'style="height: 100%;"';
$link = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css"/>
';
?>

<div class="container-fluid d-flex justify-content-center text-center" style="height: 100%;">
    <form action="/check" id="login form" method="post">
        <div class="row justify-content-center align-content-center" style="height: 100%;">
            <div class="bg-light border border-1 rounded">
                <div class="container mt-3 mb-3">
                    <h3>販売管理ログイン</h3>
                    <?php if (isset($errors)) : ?>
                        <ul class="text-danger">
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <label for="user_id" class="mt-3 col-form-label">社員番号
                    </label>
                    <div class="col-auto">
                        <input type="text" name="user_id" id="user_id" class="form-control" inputmode="numeric">
                    </div>
                    <label for="password" class="mt-3 col-form-label">パスワード
                    </label>
                    <div class="col-auto position-relative">
                        <input type="password" name="password" id="password" class="form-control">
                        <span id="buttonEye" class="translate-middle position-absolute top-50 end-0 bi bi-eye me-2" onclick="pushHideButton()"></span>
                    </div>
                    <div class="mt-3">
                        <button <?php if (is_null($reCaptchaKey)) : echo 'type="submit"';
                                elseif (isset($reCaptchaKey)) : echo 'data-sitekey=" ' . $reCaptchaKey . ' " data-callback="reCaptchaCallbackSubmit" data-action="submit"';
                                endif; ?> class="g-recaptcha btn btn-primary">ログイン
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="/src/assets/js/password.js"></script>
<?php if (isset($reCaptchaKey)) : ?>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function reCaptchaCallbackSubmit(token) {
            document.getElementById("login form").submit();
        }
    </script>
<?php endif; ?>
