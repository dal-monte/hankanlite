<?php
class ReCaptcha
{
    protected $reCaptchaKeys;

    public function connect($reCaptchaData)
    {
        // recaptchaKeyを変数に入れる
        if ($reCaptchaData['siteKey'] === '' && $reCaptchaData['secretKey'] === '') {
            $this->reCaptchaKeys = null;
        } else {
            $this->reCaptchaKeys = $reCaptchaData;
        }
    }

    public function checkSetReCaptcha()
    {
        // recaptchaキーがenvファイルに入力されているかチェック
        if (is_null($this->reCaptchaKeys)) {
            return null;
        } else {
            return $this->reCaptchaKeys['siteKey'];
        }
    }

    public function getResultReCaptcha($captchaResponse)
    {
        $secretKey = $this->reCaptchaKeys['secretKey'];

        // APIリクエスト
        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}");

        // APIレスポンス確認
        $responseData = json_decode($verifyResponse);

        //チェックが成功したか判断
        if (!$responseData->success) {
            throw new Exception('reCaptchaのチェックに失敗');
        }

        return $responseData;
    }
}
