## このアプリについて

企業でよく使用されている販売管理システムのようなものを、PHPの勉強の集大成として5ヶ月かけて作成したものです。

山浦清透氏が運営する「独学エンジニア」の最終レッスン（Lesson45）「オリジナルレッスンアプリを作ろう」の成果物として、
独学エンジニアのレッスンを通じて得た知識を元に設計しました。

構成としては、
・Lesson44の「PHPでシャッフルランチサービスを作ろう」で作成したPHPのフルスクラッチMVCモデルとDockerfileをベースとして、
・データベースにMYSQL
・画面表示にX社のBootstrap5
・MYSQL等のログイン情報保管に.env
を使用しています。

本アプリ作成の狙いは、
・Webアプリケーションの仕組みの理解
・オブジェクト指向に慣れる
・自走力の獲得
です。


## 環境構築

Dockerが存在するPCのディレクトリに本コードを保存し、
PHPのComposerをDockerファイルに導入し、
SQLデータのCREATE、INSERTを行うとブラウザのローカルホストで動作を確認できます。

使用するローカルホストのナンバーは[localhost:50080]です。
既にそのナンバーを使用中の場合は使用できません。


保存したディレクトリ直下で下記コードを入力して環境構築して下さい。

・Docker イメージをビルドする。
```bash
docker-compose build
```

・Composer(PHP)を導入する。
```bash
docker-compose exec app composer install
```

・SQLデータの作成。
```bash
docker-compose exec app php web/sql_data/all_table_set.php
```
