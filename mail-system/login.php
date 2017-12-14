<?php
require './lib/password.php';
require './lib/sql.php';
// セッション開始
session_start();
 
// エラーメッセージの初期化
$errorMessage = "";
 
// ログインボタンが押された場合
if (isset($_POST["login"])) {
  // ユーザーIDの入力チェック
  if (empty($_POST["username"])) {
    $errorMessage = "ユーザー名を入力してください。";
  } else if (empty($_POST["password"])) {
    $errorMessage = "パスワードを入力してください。";
  } 
 
  // ユーザーIDとパスワードが入力されていたら認証する
  if (!empty($_POST["username"]) && !empty($_POST["password"])) {
    $pass = "EASYSTER_EC_COORDINATE";
    $username = $_POST["username"];
    $password = $_POST["password"];
    // PostgreSQLへの接続
    $link = connect_sql();
    if (!$link) {
      $errorMessage = "データベースへの接続に失敗しました。<br>".pg_last_error();
    } else {
      $username = pg_escape_string($_POST["username"]);
      // クエリの実行
      $query = "SELECT * FROM login WHERE username = '" . $username . "'";
      $result = pg_query($query);
      if (!$result) {
        print('クエリが失敗しました。'.pg_last_error());
        $close_flag = pg_close($link);
        exit();
      }
      while ($row = pg_fetch_assoc($result)) {
        // パスワード(暗号化済み）の取り出し
        $db_hashed_pwd = decrypt($row['password'], $pass);
      }
 
      // データベースの切断
      $close_flag = pg_close($link);
 
        // 画面から入力されたパスワードとデータベースから取得したパスワードのハッシュを比較します。
       if (isset($db_hashed_pwd)){
        if ($_POST["password"] == $db_hashed_pwd) {
          // 認証成功なら、セッションIDを新規に発行する
          session_regenerate_id(true);
          $_SESSION["username"] = $_POST["username"];
          $_SESSION["last_access"] = strtotime("now");
          header("Location: index.php");
          exit;
        } else {
          // 認証失敗
          $errorMessage = "ユーザー名またはパスワードに誤りがあります。";
        }
      } else {
        $errorMessage = "ユーザー名またはパスワードに誤りがあります。";
      }
    }
  } else {
    // 未入力なら何もしない
    $username = "";
    $password = "";
  } 
} else {
  $username = "";
  $password = "";
}
?>
    <!doctype html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>イージースター メール配信システム</title>
        <link href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <style>
            * {
                font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "Noto Sans Japanese", "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, Meiryo, sans-serif;
                color: #4e4e4e;
            }

            span.err {
                color: red;
                margin-left: 12px;
            }

            body {
                background: #ffffff;
            }

            h1 {
                font-size: 24px;
            }

            .login {
                width: 400px;
                margin: 16px auto;
                font-size: 16px;
            }
            /* Reset top and bottom margins from certain elements */

            .login-header,
            .login p {
                margin-top: 0;
                margin-bottom: 0;
            }
            /* The triangle form is achieved by a CSS hack */

            .login-triangle {
                width: 0;
                margin-right: auto;
                margin-left: auto;
                border: 12px solid transparent;
                border-bottom-color: #28d;
            }

            .login-header {
                background: #28d;
                padding: 20px;
                font-size: 1.4em;
                font-weight: normal;
                text-align: center;
                text-transform: uppercase;
                color: #fff;
            }

            .login-container {
                background: #ebebeb;
                padding: 12px;
            }
            /* Every row inside .login-container is defined with p tags */

            .login p {
                padding: 12px;
            }

            .login input {
                box-sizing: border-box;
                display: block;
                width: 100%;
                border-width: 1px;
                border-style: solid;
                padding: 16px;
                outline: 0;
                font-family: inherit;
                font-size: 0.95em;
            }

            .login input[type="text"],
            .login input[type="password"] {
                background: #fff;
                border-color: #bbb;
                color: #555;
            }
            /* Text fields' focus effect */

            .login input[type="text"]:focus,
            .login input[type="password"]:focus {
                border-color: #888;
            }

            .login input[type="submit"] {
                background: #28d;
                border-color: transparent;
                color: #fff;
                cursor: pointer;
            }

            .login input[type="submit"]:hover {
                background: #17c;
            }
            /* Buttons' focus effect */

            .login input[type="submit"]:focus {
                border-color: #05a;
            }

        </style>
    </head>

    <body>
        <div class="login">
            <h1>イージースター メール配信システム</h1>
            <div class="login-triangle"></div>
            <h2 class="login-header">ログイン</h2>
            <form class="login-container" action="" method="POST">
                <span class="err"><?php echo $errorMessage ?></span>
                <p>
                    <input type="text" placeholder="ユーザー名" id="username" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>" autocomplete="off">
                </p>
                <p>
                    <input type="password" placeholder="パスワード" id="password" name="password" value="<?php echo htmlspecialchars($password, ENT_QUOTES); ?>" autocomplete="off">
                </p>
                <p>
                    <input type="submit" id="login" name="login" value="ログイン">
                </p>
            </form>
            <br />
            <div class="center"><a href="user-add.php" class="button">ユーザー登録</a></div>
        </div>
    </body>

    </html>
