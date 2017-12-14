<?php
session_start();
require './lib/sql.php';
// ログイン状態のチェック
session_check();
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>タイムテーブル - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./table-style.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
    </head>

    <body>
        <ul id="top-menu">
            <li><a href="index.php" class="button">TOP</a></li>
            <li><a href="template.php" class="button">テンプレート</a></li>
            <li><a href="member.php" class="button">顧客管理</a></li>
            <li><a href="time-table.php" class="button">タイムテーブル</a></li>
            <li><a href="logout.php" class="button">ログアウト</a></li>
        </ul>
        <div id="contents">
            <div id="main-contents" class="time-table">
                <h1>タイムテーブル</h1>
                <a href="time-table-add.php" class="button">追加</a>
                <a href="time-table-delete.php" class="button">削除</a>
            </div>
        </div>
    </body>

    </html>
