<!doctype html>
<html lang="ja">

<head>
    <?php @header("Content-Type: text/html; charset=UTF-8"); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=0.5">
    <script src="./js/jquery-1.8.2.min.js"></script>
    <script src="./js/jquery.validationEngine.js"></script>
    <script src="./js/jquery.validationEngine-ja.js"></script>
    <link href="./skins/square/pink.css" rel="stylesheet">
    <link href="./style_smp.css" rel="stylesheet">
    <script src="./js/icheck.min.js"></script>
    <script src="../js/ajaxzip3.js" charset="UTF-8"></script>
    <script src="./js/contact_view.js"></script>
    <link rel="stylesheet" href="./js/validationEngine.jquery.css">
    <script src='https://www.google.com/recaptcha/api.js'></script>
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

        <div id="formArea">
            <fieldset id="contactForm">
                <!-- <legend class="contact_form">お問い合わせフォーム</legend> -->
                <br />

                <form action="confirm.php" method="post" role="form" id="form">
                    <div class="tcell">
                        <span><label for="company_name"><strong>御社名</strong><span class="required">(必須)</span></label></span>
                        <br />
                        <span><input type="text" name="company_name" id="company_name" value="<?php echo $company_name; ?>" size="50" class="form-control textbox validate[required]" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="address1"><strong>住所</strong></label></span>
                        <br />
                        <span><input type="text" name="address1" id="address1" value="<?php echo $address1; ?>" size="50" class="form-control textbox" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="phone_number"><strong>電話番号</strong></label></span>
                        <br />
                        <span><input type="text" name="phone_number" id="phone_number" value="<?php echo $phone_number; ?>" size="20" class="form-control textbox validate[maxSize[13],minSize[10]] boxmin" placeholder="ハイフン省略可" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="name"><strong>担当者様の氏名</strong></label><span class="required">(必須)</span></span>
                        <br />
                        <span><input type="text" name="name" id="name" value="<?php echo $name; ?>" size="50" class="form-control textbox validate[required]" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="furi_name">ふりがな</label></span>
                        <br />
                        <span><input type="text" name="furi_name" id="furi_name" value="<?php echo $furi_name; ?>" size="50" class="form-control textbox" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="emaiaddl"><strong>E-mailアドレス</strong><span class="required">(必須)</span></label></span>
                        <br />
                        <span><input type="text" name="emailadd" id="emailadd" value="<?php echo $emailadd; ?>" size="50" class="form-control textbox validate[required,custom[email]]" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="emailreq">E-mailアドレス確認</label></span>
                        <br />
                        <span><input type="text" name="emailreq" id="emailreq" value="<?php echo $emailreq; ?>" size="50" class="form-control textbox validate[required,equals[emailadd]]" oncopy="return false" onpaste="return false" oncontextmenu="return false" /></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span><label for="service">興味のあるサービス</label></span>
                        <br />
                        <span>
                          <ul class="bg_radiobox">
                            <span id="service-1"><li><input type="radio" name="service" value="ChangeOver" class="form-control"<?php if($service=="ChangeOver") echo ' checked="checked"'?> /><label for="service-1" class="check">&nbsp;&nbsp;商品データ移行サービス</label></li></span>
                            <span id="service-2"><li><input type="radio" name="service" value="RegularPurchases" class="form-control"<?php if($service=="RegularPurchases") echo ' checked="checked"'?> /><label for="service-2" class="check">&nbsp;&nbsp;商品定期登録サービス</label></li></span>
                            <span id="service-3"><li><input type="radio" name="service" value="HTTPS" class="form-control"<?php if($service=="HTTPS") echo ' checked="checked"'?> /><label for="service-3" class="check">&nbsp;HTTPS化対応サービス</label></li></span>
                          </ul>
                        </span>
                    </div>
                    <span class="tcell-l">&nbsp;</span>
                    <span id="csv-attention" class="tcell-r required invisible">※管理画面でCSVが出力ができることを確認してください</span>
                    <br /><br />

                    <div class="tcell q-row invisible" id="row_url">
                        <span><label for="url" id="title_url">URL</label></span>
                        <br />
                        <span><input type="text" name="url" id="url" value="<?php echo $url; ?>" size="50" class="form-control textbox validate[custom[url]]" /></span>
                    </div>
                    <br />
                    <br />

                    <div id="sourcebody" class="tcell invisible">
                        <span><label for="source">移行元モール</label></span>
                        <span>
                          <ul class="bg_radiobox">
                            <span id="base-1"><li><input type="radio" name="source" value="base-rk" class="form-control source r_source"<?php if($source=="base-rk") echo ' checked="checked"'?> /><label for="base-1" class="check">&nbsp;&nbsp;楽天市場</label></li></span><br /><br />
                            <span id="base-2"><li><input type="radio" name="source" value="base-yh" class="form-control source r_source"<?php if($source=="base-yh") echo ' checked="checked"'?> /><label for="base-2" class="check">&nbsp;&nbsp;Yahoo! ショッピング</label></li></span><br /><br />
                            <span id="base-3"><li><input type="radio" name="source" value="base-am" class="form-control source r_source"<?php if($source=="base-am") echo ' checked="checked"'?> /><label for="base-3" class="check">&nbsp;&nbsp;Amazon</label></li></span><br /><br />
                            <span id="base-4"><li><input type="radio" name="source" value="base-pm" class="form-control source r_source"<?php if($source=="base-pm") echo ' checked="checked"'?> /><label for="base-4" class="check">&nbsp;&nbsp;ポンパレモール</label></li></span><br /><br />
                            <span id="base-5"><li><input type="radio" name="source" value="base-bd" class="form-control source r_source"<?php if($source=="base-bd") echo ' checked="checked"'?> /><label for="base-5" class="check">&nbsp;&nbsp;Wowma!</label></li></span><br /><br />
                            <span id="base-6"><li><input type="radio" name="source" value="base-ms" class="form-control source r_source"<?php if($source=="base-ms") echo ' checked="checked"'?> /><label for="base-6" class="check">&nbsp;&nbsp;MakeShop</label></li></span><br /><br />
                            <span id="base-7"><li><input type="radio" name="source" value="base-etc" class="form-control source r_source"<?php if($source=="base-etc") echo ' checked="checked"'?> /><label for="base-7" class="check">&nbsp;&nbsp;その他自社カート等</label></li></span><br /><br />
                          </ul>
                        </span>
                    </div>
                    <br /><br />
                    <div class="tcell">
                        <span>
                          <label for="source">
                            <div id="sourcebody2" class="invisible">移行先モール</div>
                          </label>
                        </span>
                        <span>
                          <ul class="bg_checkbox">
                            <span id="output-1" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="rk" class="form-control source c_output"<?php if(is_array($output)){if(in_array("rk",$output)){echo ' checked="checked"';}}?> /><label for="output-1" class="check">&nbsp;&nbsp;楽天市場</label></li></span><br /><br />
                            <span id="output-2" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="yh" class="form-control source c_output"<?php if(is_array($output)){if(in_array("yh",$output)){echo ' checked="checked"';}}?> /><label for="output-2" class="check">&nbsp;&nbsp;Yahoo! ショッピング</label></li></span><br /><br />
                            <span id="output-3" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="am" class="form-control source c_output"<?php if(is_array($output)){if(in_array("am",$output)){echo ' checked="checked"';}}?> /><label for="output-3" class="check">&nbsp;&nbsp;Amazon</label></li></span><br /><br />
                            <span id="output-4" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="pm" class="form-control source c_output"<?php if(is_array($output)){if(in_array("pm",$output)){echo ' checked="checked"';}}?> /><label for="output-4" class="check">&nbsp;&nbsp;ポンパレモール</label></li></span><br /><br />
                            <span id="output-5" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="bd" class="form-control source c_output"<?php if(is_array($output)){if(in_array("bd",$output)){echo ' checked="checked"';}}?> /><label for="output-5" class="check">&nbsp;&nbsp;Wowma!</label></li></span><br /><br />
                            <span id="output-6" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="ms" class="form-control source c_output"<?php if(is_array($output)){if(in_array("ms",$output)){echo ' checked="checked"';}}?> /><label for="output-6" class="check">&nbsp;&nbsp;MakeShop</label></li></span><br /><br />
                            <span id="output-7" class="chk_output invisible"><li><input type="checkbox" name="output[]" value="etc" class="form-control source c_output"<?php if(is_array($output)){if(in_array("etc",$output)){echo ' checked="checked"';}}?> /><label for="output-7" class="check">&nbsp;&nbsp;その他自社カート等</label></li></span><br /><br />
                          </ul>
                        </span>
                    </div>
                    <br /><br />
                    <div class="q-row tcell invisible" id="row_item">
                        <span><label for="item">対象商品点数（目安）</label></span>
                        <br />
                        <span class="tcell-goods">約 <input type="text" name="item" id="item" value="<?php echo $item; ?>" class="form-control numbox textmain validate[custom[number]]" /> 商品前後</span>
                    </div>

                    <div class="q-row invisible" id="row_discussion">
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
                    </div>

                    <br /><br />
                    <div class="tcell q-row invisible" id="row_body">
                        <span style="vertical-align: top;"><label for="body">ご質問・ご要望など</label></span>
                        <br />
                        <span><textarea name="body" id="body" cols="50" rows="8" class="form-control textbox textmain" placeholder="例：HP拝見しました。移行サービスの御見積を希望致します。"><?php echo $body; ?></textarea></span>
                    </div>

                    <br />
                    <br />

                    <div class="tcell">
                        <span>認証<!--認証（reCAPTCHA ）--></span>
                        <div class="g-recaptcha" data-sitekey="6LfxKDwUAAAAAIVlNSZUcVIZQwPSEWL63aS6jvc9"></div>

                        <!--認証ここまで）-->

                        <br /><br />

                        <div align="center"><input class="btn btn-default submit securimage" type="submit" value="確認画面へ" /></div>

                        <!--確認ページへトークンをPOSTする、隠しフィールド「ticket」-->
                        <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
                    </div>

                </form>

            </fieldset>
            <!--End of #formArea-->
        </div>

        <!--end of #contents-->
    </div>
</body>

</html>
