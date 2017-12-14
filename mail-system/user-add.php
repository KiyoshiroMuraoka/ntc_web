<html>
<meta charset="UTF-8">

<head>
    <title>ユーザー登録</title>
    <link href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="./style.css" />
    <style>
        * {
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "Noto Sans Japanese", "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, Meiryo, sans-serif;
            color: #4e4e4e;
        }

        p.err {
            color: red;
        }

        body {
            margin-top: 48px;
        }

        input.button {
            height: 42px;
            margin-top: 1px;
            margin-right: 12px;
        }

    </style>
</head>

<body>
    <?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // エラーを例外に変換する
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
require './lib/password.php';
require './lib/sql.php';
$pass = "EASYSTER_EC_COORDINATE";
$link = connect_sql();
if (!$link) {
     echo '<p class="err">接続失敗です。<br />'.pg_last_error().'</p>';
}
$result = pg_query("SELECT * FROM login");
if (!$result) {
    echo '<p class="err">SELECTクエリが失敗しました。<br />'.pg_last_error().'</p>';
}
if (isset($_POST['username']) || isset($_POST['password']) || isset($_POST['mailaddress'])) {
    if (isset($_POST['username'])){
        $username = $_POST['username'];
    } else {
        echo '<p class="err">ユーザー名は必須です。</p>';
    }
    if (isset($_POST['password'])){
        $password = $_POST['password'];
        $hashpass = encrypt($password, $pass);
    } else {
        echo '<p class="err">パスワードは必須です。</p>';
    }
    if (isset($_POST['mailaddress'])){
        $mailaddress = $_POST['mailaddress'];
    } else {
        echo '<p class="err">メールアドレスは必須です。</p>';
    }
    if (!isset($_POST['syspass']) && $_POST['syspass'] !== "20050318"){
        echo '<p class="err">登録用パスワードに誤りがあります。</p>';
    }
    try{
        if ($username != "" && $password != "" && $mailaddress != "" && $_POST['syspass'] == "20050318") {
          $sql = "INSERT INTO login (username, password, mailaddress) VALUES ('$username', '$hashpass', '$mailaddress')";
          $result_flag = pg_query($sql);
          $close_flag = pg_close($link);
          echo '<p>' . $username . ' ユーザーを登録しました。</p>';
        } else {
            echo '<p class="err">全ての項目が入力必須です。</p>';
        }
    } catch (\Exception $e){
      if(strpos($e->getMessage(),'mailaddress') !== false && strpos($e->getMessage(),'すでに存在します') !== false){
         echo '<p class="err">登録済みのメールアドレスです。別のメールアドレスを指定してください。</p>';
      }
      if(strpos($e->getMessage(),'username') !== false && strpos($e->getMessage(),'すでに存在します') !== false){
         echo '<p class="err">このユーザー名は使用できません。</p>';
      }
    }
}
?>
        <div id="user-add">
            <form action="" method="post">
                <table align="center">
                    <tr>
                        <td>ユーザー名</td>
                        <td>
                            <input type="text" name="username" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <td>パスワード</td>
                        <td>
                            <input type="text" name="password" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <td>メールアドレス</td>
                        <td>
                            <input type="text" name="mailaddress" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <td>登録用パスワード</td>
                        <td>
                            <input type="text" name="syspass" autocomplete="off">
                        </td>
                    </tr>
                </table>
                <br />
                <input type="submit" value="　　登録　　" class="button"><a href="login.php" class="button fix">ログイン画面へ戻る</a>
            </form>
        </div>
</body>

</html>
