<?php

require 'core/AutoLoader.php';

$loader = new AutoLoader();
// ここに読み込んで欲しいディレクトリを足していく
$loader->registerDir(__DIR__ . '/core');
$loader->registerDir(__DIR__ . '/controller');
$loader->registerDir(__DIR__ . '/models');
$loader->registerDir(__DIR__ . '/lib');


//　指定クラスの検索後、該当があればrequireする
$loader->register();
