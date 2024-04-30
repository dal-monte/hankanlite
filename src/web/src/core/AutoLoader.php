<?php

/**
 * classが定義されていない場合に、ファイルを探すクラス
 */
class AutoLoader
{
    //　class ファイルがあるディレクトリのリスト
    private $dirs;

    /**
     * spl_autoloadの実行
     * ()にディレクトリ名と実行時に参照するメソッド(loadClass)を入れる
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function registerDir($dir)
    {
        $this->dirs[] = $dir;
    }

    /**
     * requireするクラスが見つからなかった場合呼び出されるメソッド
     * spl_autoload_register でこのメソッドを登録する
     */
    private function loadClass($className)
    {
        foreach ($this->dirs as $dir) {
            // ファイルパスに変換しそのパスが実在すればrequireする
            $file = $dir . '/' . $className . '.php';
            if (is_readable($file)) {
                require $file;
                return;
            }
        }
    }
}
