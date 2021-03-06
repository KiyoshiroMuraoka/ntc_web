<!DOCTYPE html>
<html lang="ja">

<head>
    <title>確認 | イージースター お問い合わせフォーム - Yahoo!コマースパートナーマーケットプレイス専用</title>
    <?php @header("Content-Type: text/html; charset=UTF-8"); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <link href="./style.css" rel="stylesheet">
    <script src="./js/jquery-1.8.2.min.js"></script>
    <script>
        $(function() {
          $('html,body', parent.document).animate({ scrollTop: 0}, 500);
        });
    </script>
</head>

<body>
    <div id="contents">
        <fieldset id="contactForm">
            <!-- <legend class="contact_form">確認</legend> -->
            <div id="confirmArea">
                <p style="width:500px;">以下の内容でよろしければ送信ボタンを押してください。</p>
                <span class="tr">
                  <span class="tcell-l">御社名：</span>
                  <span class="tcell-r"><?php echo $company_name; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">住所：</span>
                  <span class="tcell-r"><?php echo $address1; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">電話番号：</span>
                  <span class="tcell-r"><?php echo $phone_number; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">担当者様の氏名：</span>
                  <span class="tcell-r"><?php echo $name; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">ふりがな：</span>
                  <span class="tcell-r"><?php echo $furi_name; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">E-mailアドレス：</span>
                  <span class="tcell-r"><?php echo $emailadd; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">URL：</span>
                  <span class="tcell-r"><?php echo $url; ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">興味のあるサービス：</span>
                  <span class="tcell-r"><?php if($service=="ChangeOverYahoo"){ echo "商品移行サービス";} ?></span>
                </span>
                <span class="tr">
	<?php
		if($service=="ChangeOverYahoo"){
				echo '<span class="tcell-l">移行元サービス：</span><span class="tcell-r">';
				switch($source){
				case 'base-rk':
					echo "楽天市場";
					break;
				case 'base-yh':
					echo "Yahoo! ショッピングモール";
					break;
				case 'base-am':
					echo "Amazon";
					break;
				case 'base-pm':
					echo "ポンパレモール";
					break;
				case 'base-bd':
					echo "Wowma";
					break;
				case 'base-ms':
					echo "MakeShop";
					break;
				case 'base-etc':
					echo "その他自社カート等";
					break;
				case 'base-other':
					echo "その他モール/カート";
					break;
				}
            echo "</span>"; } ?>
                </span>
                <span class="tr">
                  <span class="tcell-l">対象商品点数：</span>
                  <span class="tcell-r"><?php echo $item; ?>商品前後</span>
                </span>
                <span class="tr">
                    <span class="tcell-l">検討理由：</span>
                <span class="tcell-r"><?php
                    $discussionvalue = array('notselect' => "指定なし",
                                            '1' => "商戦に間に合わせたい",
                                            '2' => "新しく店舗を増やしたい",
                                            '3' => "新商品が増えたので一気に登録したい",
                                            '4' => "モール毎に商品数を揃えたい",
                                            '5' => "人が辞めてしまったため補完したい",
                                            'other' => "その他");
                    echo $discussionvalue[$discussion];
                        ?></span>
                </span>
                <span class="tr">
                  <span class="tcell-l">内容：</span>
                  <span class="tcell-r"><?php echo $body; ?></span>
                </span>
                <!--end of #confirmArea-->
            </div>
            <div align="center">
                <table border="0">
                    <tr>
                        <td>
                            <form action="contact.php" method="post">
                                <input type="submit" value="入力画面へ戻る" class="btn btn-default submit" />
                            </form>
                        </td>
                        <td>
                            <form action="success.php" method="post">
                                <!--完了ページへトークンをPOSTする、隠しフィールド「ticket」-->
                                <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
                                <input type="submit" value="送信する" class="btn btn-default submit" />
                            </form>
                        </td>
                    </tr>
                </table>
            </div>
        </fieldset>
        <!--end of #contents-->
    </div>
