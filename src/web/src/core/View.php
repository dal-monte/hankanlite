<?php

class View
{
    protected $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function render($path, $variables, $layout = false)
    {
        extract($variables);
        // 上記コードによって下記のように変換される
        //　[ 'groups' => [] ]
        //　$groups = [];

        ob_start();
        require $this->baseDir . '/' . $path . '.php'; //左記は require 'views/product/index.php' のように変換される
        $content = ob_get_clean();

        ob_start();
        require $this->baseDir . '/' . $layout . '.php';
        $layout = ob_get_clean();

        return $layout;
    }
}
