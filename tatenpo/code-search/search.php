<?php

error_reporting(0);
//セキュリティ対策系
//CSRF対策…性質上必要はない
//XSS対策…JANコード入力欄がそのままURLに乗っかるため、ctype_digitで数字か判別している→エラーなら終了

//楽天WebサービスのSDKファイルのため、読み込み必須
require_once './rws-php-sdk/autoload.php';

//自分のファイル名
$programfile = 'search.php';

//商品ディレクトリ一覧リストファイル名
$genrelistfile = 'ichiba_genre_list.csv';

//Yahooプロダクトカテゴリファイル名
$productcategoryfile = 'product_category_data.csv';

function h($str) {
	return htmlspecialchars($str, ENT_QUOTES);
}

if (!empty($_GET['code']) && ctype_digit($_GET['code'])) {
	if ($_GET['pageview'] == 'true'){
		//Web上で検索をかけた場合は、インターフェースを表示する
		$rk = "";
		$yh = "";
		$am = "";
		if ($_GET['shop'] == "rk"){
			$rk = ' checked=""';
		} elseif ($_GET['shop'] == "yh"){
			$yh = ' checked=""';
		} elseif ($_GET['shop'] == "am"){
			$am = ' checked=""';
		}
		
		if (ctype_digit($_GET['itempage'])) {
			$itempage = $_GET['itempage'];
		} else {
			$itempage = "";
		}
		echo <<< EOM
<!DOCTYPE html>
<html lang="ja">
<title>ディレクトリID候補検索</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />
<link href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" rel="stylesheet" />
<script type="text/javascript" src="jquery-3.2.1.slim.min.js"></script>
<style>
* {
	font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "Noto Sans Japanese", "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, Meiryo, sans-serif;
	color: #4e4e4e;
}
form,div{
	text-align: center;
}
div#title {
	font-size: 1.5em;
	font-weight: bold;
}
div#desc {
	font-size: 0.9em;
}
div#err {
	color: #FF0000;
}
div#date {
	margin-top: 24px;
	text-align: right;
	font-size: 0.75em;
}
table#result {
	margin: 0 auto;
}
table#result tr td {
	border: 1px solid #4e4e4e;
	font-size: 0.8em;
	padding: 6px;
}
table#result tr td.name {
	background-color: #D5E0F1;
}
table#result tr td.value {
	background-color: #F9DFD5;
}
td.titlename, td.titlevalue {
	color: #FFFFFF;
	font-weight: bold;
}
td.titlename {
	background-color: #8BA7D5;
}
td.titlevalue {
	background-color: #EDA184;
}
input#code {
	text-align: center;
	width: 130px;
}
input#itempage {
	text-align: right;
	width: 38px;
}
button:disabled {
	color: #cacaca;
}
#load {
	margin: 0 auto;
}
</style>
<script>
$(function(){
    $("#search")
        .click(function(){
            $("#load").css("display","block");
        });
});
</script>
<body>
<div id="title">ディレクトリID候補検索ツール</div>
<div id="desc">JANコードに紐付いている、ディレクトリIDの一覧を出力するツールです。<br />
※ディレクトリIDに紐付けされている該当商品が多いほど上部に表示されます。</div>
<form action="{$programfile}" method="get" id="search-form">
<p>対象モール<br><input type="radio" name="shop" id="select1" value="rk"{$rk}><label for="select1">楽天市場</label>
<input type="radio" name="shop" id="select2" value="yh"{$yh}><label for="select2">Yahoo!ショッピング</label><br>
<input type="radio" name="shop" id="select3" value="am"{$am}><label for="select3">Amazon.co.jp</label>
<input type="radio" name="shop" id="select4" value="wowma" disabled=""><label for="select4">Wowma!（未対応）</label>
</p>
<p><label>JANコード：<input type="number" name="code" id="code" maxlength="13" value="{$_GET['code']}"></label></p>
<p><label>対象件数：<input type="number" name="itempage" id="itempage" maxlength="2" value="{$itempage}" min="1" max="10">0件 ※Amazonのみ</label></p>
<input type="hidden" name="pageview" value="true">
<p><button id="search">　‡　　　検索　　　‡　</button></p>
</form>
<img src="load.gif" id="load" style="display:none;" />
EOM;
	}

	//楽天市場処理

	if ($_GET['shop'] == "rk") {
		//楽天ウェブサービス用クライアントの初期化
		$client = new RakutenRws_Client();

		// アプリID (デベロッパーID) をセットします
		$client->setApplicationId('1026922746856524248');

		// アフィリエイトID をセットします(任意)
		$client->setAffiliateId('15546fdf.9683c0db.15546fe0.54c3d682');

		// IchibaItem/Search API から、keyword=JANコード を検索します
		$response = $client->execute('IchibaItemSearch', array(
		  'keyword' => $_GET['code'] //4901306080344
		));

		// レスポンスが正しいかを isOk() で確認することができます
		if ($response->isOk()) {
			// 配列アクセスによりレスポンスにアクセス。
			foreach ($response as $item) {
				$genreId = $genreId.$item['genreId'].",";
			}
			$genreId = $genreId.$item['genreId'].",";
			// 一旦配列化
			$record = explode(",",substr($genreId, 0, -1));
			// ヒット数カウント
			$result = array_count_values($record);
			// 多い順にソート
			arsort($result);
			// 重複削除
			$unique = array_unique(array_keys($result));
			// 空データ部分を整理
			$alignedUnique = array_values($unique);

			if ($_GET['pageview'] == 'true'){
				// カテゴリ名抽出

				//ディレクトリ一覧ファイルのタイムスタンプを表示
				$filedate = "<div id=\"date\">[".$genrelistfile."] Last modified date: ".date("Y/m/d H:i:s", filemtime($genrelistfile))."</div>";

				//ディレクトリ一覧ファイルを配列へ読み込み
				$csv  = array();
				$buffer = mb_convert_encoding(file_get_contents($genrelistfile), "UTF-8", "sjis-win");
				$fp = tmpfile();
				fwrite($fp, $buffer);
				rewind($fp);

				$list = array();
				while (($data = fgetcsv($fp, 0, ",")) !== FALSE) {
					$csv[] = $data;
				}
				fclose($fp);

				//ディレクトリ一覧配列の行数を計算
				$max = count($csv);

				//JANコード検索がヒットしたら中身を表示する
				if (!empty($alignedUnique[0])) {
					echo "<table id=\"result\">\n<tr><td class=\"titlename\">ディレクトリ名</td><td class=\"titlevalue\">ディレクトリID</td></tr>\n";
					foreach ($alignedUnique as $genre) {
						for ($i = 1; $i < $max; $i++) {
							if ($csv[$i][0] == $genre) {
								echo "<tr><td class=\"name\">".$csv[$i][1]."</td><td class=\"value\">".$genre."</td></tr>\n";
								break;
							}
						}
					}
					echo "</table>\n\n{$filedate}\n</body>\n</html>";
				} else {
					echo "<div id=\"err\">検索にヒットしませんでした。</div>\n<br />\n{$filedate}\n</body>\n</html>";
				}
			} else {
				echo implode(",",$alignedUnique);
			}
		} else {
			echo 'NG';// 'Error:'.$response->getMessage();
		}
	} elseif ($_GET['shop'] == "yh") {
		//Yahoo処理
		$api = 'http://shopping.yahooapis.jp/ShoppingWebService/V1/itemSearch?appid=dj0zaiZpPWdCRFNPOE1KYWU3UCZzPWNvbnN1bWVyc2VjcmV0Jng9MTQ-&hits=50&jan='.$_GET['code'];
		//レスポンスをXML形式で受け取る
		$xml = @simplexml_load_file($api);
		$hits = $xml->Result->Hit;
		$codes = "";
		$ids = "";
		//商品コードを取り出す
		foreach ($hits as $hit) {
			$codes = $codes.h($hit->Code).",";
		}
		//連結した文字列を配列に
		$codelist = array();
		$codelist = explode(",", $codes);
		//商品コードにて商品詳細をAPIリクエスト
		foreach ($codelist as $code) {
			$api2 = 'http://shopping.yahooapis.jp/ShoppingWebService/V1/itemLookup?appid=dj0zaiZpPWdCRFNPOE1KYWU3UCZzPWNvbnN1bWVyc2VjcmV0Jng9MTQ-&itemcode='.$code.'&responsegroup=large';
			$xml2 = @simplexml_load_file($api2);
			$hits = $xml2->Result->Hit->ProductCategory;
			foreach ($hits as $hit) {
				//プロダクトカテゴリIDをカンマ形式で保存
				$ids = $ids.h($hit->ID).",";
			}
		}
		// 一旦配列化
		$record = explode(",",substr($ids, 0, -1));
		// ヒット数カウント
		$result = array_count_values($record);
		// 多い順にソート
		arsort($result);
		// 重複削除
		$unique = array_unique(array_keys($result));
		// 空データ部分を整理
		$alignedUnique = array_values($unique);

		if ($_GET['pageview'] == 'true'){
			// カテゴリ名抽出

			//ディレクトリ一覧ファイルのタイムスタンプを表示
			$filedate = "<div id=\"date\">[".$productcategoryfile."] Last modified date: ".date("Y/m/d H:i:s", filemtime($productcategoryfile))."</div>";

			//ディレクトリ一覧ファイルを配列へ読み込み
			$csv  = array();
			$buffer = mb_convert_encoding(file_get_contents($productcategoryfile), "UTF-8", "sjis-win");
			$fp = tmpfile();
			fwrite($fp, $buffer);
			rewind($fp);

			$list = array();
			while (($data = fgetcsv($fp, 0, ",")) !== FALSE) {
				$csv[] = $data;
			}
			fclose($fp);

			//ディレクトリ一覧配列の行数を計算
			$max = count($csv);

			//JANコード検索がヒットしたら中身を表示する
			if (!empty($alignedUnique[0])) {
				echo "<table id=\"result\">\n<tr><td class=\"titlename\">カテゴリ名</td><td class=\"titlevalue\">プロダクトカテゴリID</td></tr>\n";
				foreach ($alignedUnique as $cat) {
					for ($i = 1; $i < $max; $i++) {
						if ($csv[$i][0] == $cat) {
							echo "<tr><td class=\"name\">".$csv[$i][2]."</td><td class=\"value\">".$cat."</td></tr>\n";
							break;
						}
					}
				}
				echo "</table>\n\n{$filedate}\n</body>\n</html>";
			} else {
				echo "<div id=\"err\">検索にヒットしませんでした。</div>\n<br />\n{$filedate}\n</body>\n</html>";
			}
		} else {
			echo implode(",",$alignedUnique);
		}
	} elseif ($_GET['shop'] == "am") {

		if ($_GET['itempage'] <> "") {
			// Amazon APIのアクセスキーとシークレットキー、JANコードを入力
			define("Access_Key_ID", "AKIAJ33DEWL6XYZPZBVA");
			define("Secret_Access_Key", "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");
			define("Associate_tag", "ntcec-20");
			define("JANCode", $_GET['code']);
			
			$bn_name = "";
			$bn_id = "";
			
			try {
				for ($i = 1; $i < $_GET['itempage']; $i++) {
					// AmazonAPIは1秒間に1回のリクエストのみ受け付けるため、念の為2秒ウェイトを入れる
					sleep(2);
					// AmazonAPI用プログラム読み込み
					include('./amazon_itemlookup.php');

					// AmazonAPIリクエスト
					$amazon_xml = simplexml_load_string(file_get_contents($base_request));

					// 初期化
					$browse_nodes = "";
					$browse_node_names = "";

					// 結果が問題なく返ってきたら処理を続ける
					if ($amazon_xml->Items->Request->IsValid == "True") {
						// 商品の数分ループ
						foreach ($amazon_xml->Items->Item as $item) {
							// ブラウズノードを格納
							$browse_node = $item->BrowseNodes->BrowseNode->BrowseNodeId;
							$browse_nodes = $browse_nodes.$browse_node.",";

							$isEOB = true;

							// ブラウズノード名を格納する準備をする
							$nextTag = $item->BrowseNodes->BrowseNode;
							$isNextTag = array_key_exists('Ancestors', $nextTag);

							if ($nextTag->Name <> "カテゴリー別") {
								$browse_node_name = $nextTag->Name;
							} else {
								$browse_node_name = "";
							}

							// Ancestorsが下層にある限り、取得を続ける
							while ($isEOB) {
								if($isNextTag) {
									// ブラウズノード名を格納
									$nextTag = $nextTag->Ancestors->BrowseNode;
									// 何故か上層から順番にブラウズノードの下層データが来るというへんてこりん仕様のため、
									// 前回取得した内容の前にどんどんくっつけていく
									if ($nextTag->Name <> "カテゴリー別") {
										if ($browse_node_name <> "") {
											$browse_node_name = $nextTag->Name." > ".$browse_node_name;
										} else {
											$browse_node_name = $nextTag->Name;
										}
									}
									$isNextTag = array_key_exists('Ancestors', $nextTag);
								} else {
									// Ancestorsが下層になければ、Name取得は終了
									$isEOB = false;
								}
							}
							$browse_node_names = $browse_node_names.$browse_node_name.",";
						}
						// ブラウズノードIDを一旦配列化
						$record = explode(",",substr($browse_nodes, 0, -1));
						// ヒット数カウント
						$result = array_count_values($record);
						// 多い順にソート
						arsort($result);
						// 重複削除
						$unique = array_unique(array_keys($result));
						// 空データ部分を整理
						$alignedUnique = array_values($unique);

						// ブラウズノード名を一旦配列化
						$record2 = explode(",",substr($browse_node_names, 0, -1));
						// ヒット数カウント
						$result2 = array_count_values($record2);
						// 多い順にソート
						arsort($result2);
						// 重複削除
						$unique2 = array_unique(array_keys($result2));
						// 空データ部分を整理
						$alignedUnique2 = array_values($unique2);

						// JANコード検索がヒットしたら中身を表示する
						if (!empty($alignedUnique[0])) {
							// データ数を計算
							$bn_name = $bn_name.implode(",", $alignedUnique2).",";
							$bn_id = $bn_id.implode(",", $alignedUnique).",";
						}
					}
				}
				// ブラウズノード名、IDを件数分格納しているが、このままだと重複データが排出されてしまう
				// そのため、ブラウズノード名を一時配列格納→ブラウズノード名配列を連想配列化→ブラウズノードIDをソート＆重複削除→表示　を行う
				$ids = array();
				$names = array();
				$_name = array();
				$ids = explode(",", substr($bn_id, 0, -1));
				// 一時配列へブラウズノード名を格納
				$_name = explode(",", substr($bn_name, 0, -1));
				if (count($_name) > 0) {
					$max = count($_name);
					for ($i = 0; $i < $max; $i++) {
						// ブラウズノード名を一旦連想配列化
						$names[$ids[$i]] = $_name[$i];
					}
					// ヒット数カウント
					$result = array_count_values($ids);
					// 多い順にソート
					arsort($result);
					// 重複削除
					$unique = array_unique(array_keys($result));
					// 空データ部分を整理
					$alignedUnique = array_values($unique);

					// IDソートが終わったため、表示
					$max = count($alignedUnique);
					if ($max == 0) {
						if ($_GET['pageview'] == 'true'){
							echo "<div id=\"err\">検索にヒットしませんでした。</div>\n<br />\n\n</body>\n</html>";
						} else {
							echo "NG";
						}
					} else {
						if ($_GET['pageview'] == 'true'){
							for ($i = 0; $i < $max; $i++) {
								if (!($max == 0) && $i == 0) {echo "<table id=\"result\">\n<tr><td class=\"titlename\">ブラウズノード名</td><td class=\"titlevalue\">ブラウズノードID</td></tr>\n";}
								echo "<tr><td class=\"name\">".$names[$alignedUnique[$i]]."</td><td class=\"value\">".$alignedUnique[$i]."</td></tr>\n";
							}
						} else {
							echo implode(",",$alignedUnique);
						}
					}
				} else {
					if ($_GET['pageview'] == 'true') {
						echo "<div id=\"err\">検索にヒットしませんでした。</div>\n<br />\n\n</body>\n</html>";
					} else {
						echo "NG";
					}
				}
				if ($_GET['pageview'] == 'true') { echo "</table>\n\n</body>\n</html>"; }
			} catch (Exception $e) {
				echo "<div id=\"err\">例外エラー: ",  $e->getMessage(), "</div>\n<br />\n\n</body>\n</html>";
			}
		} else {
			if ($_GET['pageview'] == 'true'){
				echo "<div id=\"err\">対象件数は必ず入力してください。</div>\n<br />\n\n</body>\n</html>";
			} else {
				echo "NG";
			}
		}
	} else {
		echo "NG";
	}
} else {
	// JANコードがない時の処理
	$err = '';
	if (isset($_GET['pageview'])) {
		if ($_GET['pageview'] == 'true') {
			// URL直呼び出しでない時にJANコードがなければエラーメッセージを表示
			$err = '<div id="err">JANコードを入力してください。</div>';
		}
	}
	// URL直呼び出し出ない時はインターフェースを表示する
	echo <<< EOM
<!DOCTYPE html>
<html lang="ja">
<head>
<title>ディレクトリID候補検索</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />
<link href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" rel="stylesheet" />
<script type="text/javascript" src="jquery-3.2.1.slim.min.js"></script>
<style>
* {
	font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "Noto Sans Japanese", "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, Meiryo, sans-serif;
	color: #4e4e4e;
}
form,div{
	text-align: center;
}
div#title {
	font-size: 1.5em;
	font-weight: bold;
}
div#desc {
	font-size: 0.9em;
}
div#err {
	color: #FF0000;
}
table#result {
	margin: 0 auto;
}
table#result tr td {
	border: 1px solid #4e4e4e;
	font-size: 0.8em;
	padding: 6px;
}
table#result tr td.name {
	background-color: #D5E0F1;
}
table#result tr td.value {
	background-color: #F9DFD5;
}
td.titlename, td.titlevalue {
	color: #FFFFFF;
	font-weight: bold;
}
td.titlename {
	background-color: #8BA7D5;
}
td.titlevalue {
	background-color: #EDA184;
}
input#code {
	text-align: center;
	width: 130px;
}
input#itempage {
	text-align: right;
	width: 38px;
}
button:disabled {
	color: #cacaca;
}
#load {
	margin: 0 auto;
}
</style>
<script>
$(function(){
    $("#search")
        .click(function(){
            $("#load").css("display","block");
        });
});
</script>
<body>
<div id="title">ディレクトリID候補検索ツール</div>
<div id="desc">JANコードに紐付いている、ディレクトリIDの一覧を出力するツールです。<br />
※ディレクトリIDに紐付けされている該当商品が多いほど上部に表示されます。</div>
<form action="{$programfile}" method="get" id="search-form">
<p>対象モール：<input type="radio" name="shop" id="select1" value="rk" checked=""><label for="select1">楽天市場</label>
<input type="radio" name="shop" id="select2" value="yh"><label for="select2">Yahoo!ショッピング</label>
<input type="radio" name="shop" id="select3" value="am"><label for="select3">Amazon.co.jp</label>
<input type="radio" name="shop" id="select4" value="wowma" disabled=""><label for="select4">Wowma!（未対応）</label>
</p>
<p><label>JANコード：<input name="code" id="code" maxlength="13"></label></p>
<p><label>対象件数：<input name="itempage" id="itempage" maxlength="2" value="3" min="1" max="10">0件 ※Amazonのみ</label></p>
<input type="hidden" name="pageview" value="true">
<p><button id="search">　‡　　　検索　　　‡　</button></p>
</form>
<img src="load.gif" id="load" style="display:none;" />
{$err}
</body>
</html>
EOM;
}
?>