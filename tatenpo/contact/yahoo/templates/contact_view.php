<!DOCTYPE html>
<html lang="ja">

<head>
    <?php @header("Content-Type: text/html; charset=UTF-8"); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <script src="./js/jquery-1.8.2.min.js"></script>
    <script src="./js/jquery.validationEngine.js"></script>
    <script src="./js/jquery.validationEngine-ja.js"></script>
    <link href="./skins/square/pink.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
    <script src="./js/icheck.min.js"></script>
    <script src="../../js/ajaxzip3.js" charset="UTF-8"></script>
    <link rel="stylesheet" href="./js/validationEngine.jquery.css">
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <title>イージースター お問い合わせフォーム - Yahoo!コマースパートナーマーケットプレイス専用</title>
    <script>
        $(function() {
            jQuery("#form").validationEngine();
        });
    </script>
</head>

<body>
    <div id="contents">
        <!--入力値にエラーがあった場合エラーメッセージを表示-->
        <div id="errorDispaly">
<?php
  if(isset($error)) {
    echo implode('<br />', $error);
    echo '<br />';
  }
?>
                <!--End of #errorDispaly-->
        </div>
        <div class="contact_form">
            <br />※出来るだけ事前に商品データ（CSV）とログイン情報等のご用意をお願い致します。<br /><br />
        </div>
        <div id="formArea">
            <fieldset id="contactForm">
                <br />
                <form action="confirm.php" method="post" role="form" id="form">
                    <span class="tcell-l"><label for="company_name"><strong>御社名</strong><span class="required">&nbsp;(必須)</span></label></span>
                    <span class="tcell-r"><input type="text" name="company_name" id="company_name" value="<?php echo $company_name; ?>" size="50" class="form-control textbox validate[required]" /></span>

                    <br />

                    <span class="tcell-l"><label for="address1">住所</label></span>
                    <span class="tcell-r"><input type="text" name="address1" id="address1" value="<?php echo $address1; ?>" size="50" class="form-control textbox" /></span>

                    <br />

                    <span class="tcell-l"><label for="phone_number">電話番号</label></span>
                    <span class="tcell-r"><input type="text" name="phone_number" id="phone_number" value="<?php echo $phone_number; ?>" size="20" class="form-control textbox validate[maxSize[13],minSize[10]] boxmin" placeholder="ハイフン省略可" /></span>

                    <br />

                    <span class="tcell-l"><label for="department">所属部署</label></span>
                    <span class="tcell-r"><input type="text" name="department" id="department" value="<?php echo $department; ?>" size="50" class="form-control textbox boxmin" /></span>

                    <br />

                    <span class="tcell-l"><label for="name">担当者様の氏名</label></span>
                    <span class="tcell-r"><input type="text" name="name" id="name" value="<?php echo $name; ?>" size="50" class="form-control textbox" /></span>

                    <br />

                    <span class="tcell-l"><label for="furi_name">ふりがな</label></span>
                    <span class="tcell-r"><input type="text" name="furi_name" id="furi_name" value="<?php echo $furi_name; ?>" size="50" class="form-control textbox" /></span>

                    <br />

                    <span class="tcell-l"><label for="emaiaddl"><strong>E-mailアドレス</strong><span class="required">&nbsp;(必須)</span></label></span>
                    <span class="tcell-r"><input type="text" name="emailadd" id="emailadd" value="<?php echo $emailadd; ?>" size="50" class="form-control textbox validate[required,custom[email]]" /></span>

                    <br />

                    <span class="tcell-l"><label for="emailreq">E-mailアドレス確認</label></span>
                    <span class="tcell-r"><input type="text" name="emailreq" id="emailreq" value="<?php echo $emailreq; ?>" size="50" class="form-control textbox validate[required,equals[emailadd]]" oncopy="return false" onpaste="return false" oncontextmenu="return false" /></span>

                    <br />

                    <span class="tcell-l"><label for="url">ショップURL</label></span>
                    <span class="tcell-r"><input type="text" name="url" id="url" value="<?php echo $url; ?>" size="50" class="form-control textbox validate[custom[url]]" /></span>

                    <br />

                    <span class="tcell-l"><label for="service">興味のあるサービス</label></span>
                    <span class="tcell-r">
                      <ul class="bg_radiobox">
                        <span id="service-1"><li><input type="radio" name="service" value="ChangeOverYahoo" class="form-control"<?php if($service=="ChangeOverYahoo") echo ' checked="checked"'?> /><label for="service-1" class="check">&nbsp;商品データ移行サービス</label></li></span><br />
                      </ul>
                    </span>

                    <div id="sourcebody">
                        <span class="tcell-l"><label for="source">移行元モール</label></span>
                        <span class="tcell-r">
                          <ul class="bg_radiobox">
                              <span id="base-1"><li><input type="radio" name="source" value="base-rk" class="form-control source"<?php if($source=="base-rk") echo ' checked="checked"'?> /><label for="base-1" class="check">&nbsp;楽天市場</label></li></span><br />
                              <span id="base-2"><li><input type="radio" name="source" value="base-yh" class="form-control source"<?php if($source=="base-yh") echo ' checked="checked"'?> /><label for="base-2" class="check">&nbsp;Yahoo! ショッピング</label></li></span><br />
                              <span id="base-3"><li><input type="radio" name="source" value="base-other" class="form-control source"<?php if($source=="base-other") echo ' checked="checked"'?> /><label for="base-3" class="check">&nbsp;その他モール/カート</label></li></span><br />
                          </ul>
                        </span>
                    </div>

                    <br />

                    <span class="tcell-l"><label for="item">対象商品点数（目安）</label></span>
                    <span class="tcell-r">約 <input type="text" name="item" id="item" value="<?php echo $item; ?>" class="form-control numbox textmain validate[custom[number]]" /> 商品前後</span>

                    <br />
                    <span class="tcell-l">&nbsp;</span>

                    <br />

                    <span class="tcell-l"><label for="discussion">検討理由</label></span>
                    <span class="tcell-r">
                        <select name="discussion" id="discussion" class="selectbox">
                            <option value="notselect">▼選択してください▼</option>
                            <option <?php if($discussion == '1') echo 'selected '?>value="1">商戦に間に合わせたい</option>
                            <option <?php if($discussion == '2') echo 'selected '?>value="2">新しく店舗を増やしたい</option>
                            <option <?php if($discussion == '3') echo 'selected '?>value="3">新商品が増えたので一気に登録したい</option>
                            <option <?php if($discussion == '4') echo 'selected '?>value="4">モール毎に商品数を揃えたい</option>
                            <option <?php if($discussion == '5') echo 'selected '?>value="5">人が辞めてしまったため補完したい</option>
                            <option <?php if($discussion == 'other') echo 'selected '?>value="other">その他</option>
                        </select>
                    </span>

                    <br />

                    <span class="tcell-l" style="vertical-align: top;"><label for="body">ご質問・ご要望など</label></span>
                    <span class="tcell-r"><textarea name="body" id="body" cols="50" rows="8" class="form-control textbox textmain" placeholder="例：Yahoo!コマースパートナーマーケットプレイスよりご依頼させて頂きました。よろしくお願いします。"><?php echo $body; ?></textarea></span>

                    <br />

                    <!--認証（reCAPTCHA）-->
                    <span class="tcell-l">認証</span>
                    <span class="tcell-r">
                        <div class="g-recaptcha" data-sitekey="6LfxKDwUAAAAAIVlNSZUcVIZQwPSEWL63aS6jvc9"></div>
                    </span>
                    <!--認証ここまで）-->

                    <!--画像認証ここまで）-->
                    <br />

                    <div align="center"><input class="btn btn-default submit" type="submit" value="確認画面へ" /></div>

                    <!--確認ページへトークンをPOSTする、隠しフィールド「ticket」-->
                    <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
                    <input type="hidden" name="estimate" value="" />
                </form>

            </fieldset>
            <!--End of #formArea-->
        </div>

        <!--end of #contents--><br /><br /><br /><br /><br /><br /> </div>
</body>

</html>
