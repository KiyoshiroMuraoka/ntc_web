<!DOCTYPE html>
<html lang='ja'>
<head>
<title>ストアカテゴリ情報</title>
<meta charset='utf-8'>
</head>
<body>
<table border="1">
<tr>
<td>ParentCategory</td>
<td>PageID</td>
<td>PagePath</td>
<td>PageURL</td>
<td>Name</td>
<td>Display</td>
<td>HiddenPageFlag</td>
<td>SortOrder</td>
<td>UpdateTime</td>
<td>TreeDepth</td>
</tr>
<?php
// アプリケーション設定
define('CONSUMER_KEY', 'dj0zaiZpPTlZZWl6WUJYeXpmMCZzPWNvbnN1bWVyc2VjcmV0Jng9YjI-');
define('CONSUMER_SECRET', '663e5f7a103d5134927191bbaea1c5351baff3d0');
define('CALLBACK_URL', 'http://www.technocrats.jp/tatenpo/test/yh_cate.php');

// ショップ情報
define('SellerID', 'futonpapa');

// URL
define('TOKEN_URL', 'https://auth.login.yahoo.co.jp/yconnect/v1/token');
define('INFO_URL', 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/stCategoryList?seller_id='.SellerID);

//--------------------------------------
// アクセストークンの取得
//--------------------------------------
$params = array(
	'code'			=> $_GET['code'],
	'grant_type'	=> 'authorization_code',
	'redirect_uri'	=> CALLBACK_URL,
);

// POST送信
$options = array('http' => array(
	'method' => 'POST',
	'content' => http_build_query($params),
	'header' => 'Authorization: Basic ' . base64_encode(CONSUMER_KEY.':'.CONSUMER_SECRET) . "\r\nContent-Type: application/x-www-form-urlencoded",
));
$res = file_get_contents(TOKEN_URL, false, stream_context_create($options));

// レスポンス取得
$token = json_decode($res, true);
if(isset($token['error'])){
	echo 'エラー発生';
	exit;
}
$access_token = $token['access_token'];


//--------------------------------------
// 取得してみる
//--------------------------------------
$options = array('http' => array(
	'method' => 'GET',
    'host' => 'circus.shopping.yahooapis.jp',
	'header' => 'Authorization: Bearer ' . $access_token,
));
$res = file_get_contents(INFO_URL, false, stream_context_create($options));
$res = str_replace("<![CDATA[", "", $res);
$res = str_replace("]]>", "", $res);
$xmlObject = simplexml_load_string($res);
$xmlArray = json_decode( json_encode( $xmlObject ), TRUE ) ;
for ($i=0; $i < $xmlArray['@attributes']['totalResultsAvailable']; $i++){
	$child_res = file_get_contents(INFO_URL."&page_key=".$xmlArray["Result"][$i]["PageKey"], false, stream_context_create($options));
	$child_res = str_replace("<![CDATA[", "", $child_res);
	$child_res = str_replace("]]>", "", $child_res);
	$child_xmlObject = simplexml_load_string($child_res);
	$child_xmlArray = json_decode( json_encode( $child_xmlObject ), TRUE ) ;
	$pagekey = $xmlArray["Result"][$i]["PageKey"];
	$name = $xmlArray["Result"][$i]["Name"];
	$path = $name;
	$display = $xmlArray["Result"][$i]["Display"];
	$hiddenpageflag = $xmlArray["Result"][$i]["HiddenPageFlag"];
	$sortorder = $xmlArray["Result"][$i]["SortOrder"];
	$updatetime = $xmlArray["Result"][$i]["UpdateTime"];
	echo "<tr><td></td><td>".$pagekey."</td><td>".$path."</td><td><a href='https://store.shopping.yahoo.co.jp/".SellerID."/".$pagekey.".html'>https://store.shopping.yahoo.co.jp/".SellerID."/".$pagekey.".html</a></td><td>".$name."</td><td>".$display."</td><td>".$hiddenpageflag."</td><td>".$sortorder."</td><td>".$updatetime."</td><td>0</td></tr>";
	for ($j=0; $j < $child_xmlArray['@attributes']['totalResultsAvailable']; $j++){
		if(!empty($child_xmlArray["Result"][$j]["Name"])){
			$child_name = $child_xmlArray["Result"][$j]["Name"];
			$child_path = $path . ":" . $child_name;
		}else{
			$child_name = "";
			$child_path = "";
		}
		if(!empty($child_xmlArray["Result"][$j]["PageKey"])){$child_pagekey = $child_xmlArray["Result"][$j]["PageKey"];}else{$child_pagekey = "";}
		if(!empty($child_xmlArray["Result"][$j]["Display"])){$child_display = $child_xmlArray["Result"][$j]["Display"];}else{$child_display = "";}
		if(!empty($child_xmlArray["Result"][$j]["HiddenPageFlag"])){$child_hiddenpageflag = $child_xmlArray["Result"][$j]["HiddenPageFlag"];}else{$child_hiddenpageflag = "";}
		if(!empty($child_xmlArray["Result"][$j]["SortOrder"])){$child_sortorder = $child_xmlArray["Result"][$j]["SortOrder"];}else{$child_sortorder = "";}
		if(!empty($child_xmlArray["Result"][$j]["UpdateTime"])){$child_updatetime = $child_xmlArray["Result"][$j]["UpdateTime"];}else{$child_updatetime = "";}
		if(!empty($child_pagekey)){echo "<tr><td>".$pagekey."</td><td>".$child_pagekey."</td><td>".$child_path."</td><td><a href='https://store.shopping.yahoo.co.jp/".SellerID."/".$child_pagekey.".html'>https://store.shopping.yahoo.co.jp/".SellerID."/".$child_pagekey.".html</a></td><td>".$child_name."</td><td>".$child_display."</td><td>".$child_hiddenpageflag."</td><td>".$child_sortorder."</td><td>".$child_updatetime."</td><td>1</td></tr>";}
		if(!empty($child_xmlArray["Result"][$j]["PageKey"])){
			$child2_res = file_get_contents(INFO_URL."&page_key=".$child_xmlArray["Result"][$j]["PageKey"], false, stream_context_create($options));
			$child2_res = str_replace("<![CDATA[", "", $child2_res);
			$child2_res = str_replace("]]>", "", $child2_res);
			$child2_xmlObject = simplexml_load_string($child2_res);
			$child2_xmlArray = json_decode( json_encode( $child2_xmlObject ), TRUE ) ;
			for ($k=0; $k < $child2_xmlArray['@attributes']['totalResultsAvailable']; $k++){
				if(!empty($child2_xmlArray["Result"][$k]["PageKey"])){$child2_pagekey = $child2_xmlArray["Result"][$k]["PageKey"];}else{$child2_pagekey ="";}
				if(!empty($child2_xmlArray["Result"][$k]["Name"])){
					$child2_name = $child2_xmlArray["Result"][$k]["Name"];
					$child2_path = $child_path . ":" . $child2_name;
				}else{
					$child2_name = "";
					$child2_path = "";
				}
				if(!empty($child2_xmlArray["Result"][$k]["Display"])){$child2_display = $child2_xmlArray["Result"][$k]["Display"];}else{$child2_display = "";}
				if(!empty($child2_xmlArray["Result"][$k]["HiddenPageFlag"])){$child2_hiddenpageflag = $child2_xmlArray["Result"][$k]["HiddenPageFlag"];}else{$child2_hiddenpageflag = "";}
				if(!empty($child2_xmlArray["Result"][$k]["SortOrder"])){$child2_sortorder = $child2_xmlArray["Result"][$k]["SortOrder"];}else{$child2_sortorder = "";}
				if(!empty($child2_xmlArray["Result"][$k]["UpdateTime"])){$child2_updatetime = $child2_xmlArray["Result"][$k]["UpdateTime"];}else{$child2_updatetime = "";}
				if(!empty($child2_pagekey)){echo "<tr><td>".$child_pagekey."</td><td>".$child2_pagekey."</td><td>".$child2_path."</td><td><a href='https://store.shopping.yahoo.co.jp/".SellerID."/".$child2_pagekey.".html'>https://store.shopping.yahoo.co.jp/".SellerID."/".$child2_pagekey.".html</a></td><td>".$child2_name."</td><td>".$child2_display."</td><td>".$child2_hiddenpageflag."</td><td>".$child2_sortorder."</td><td>".$child2_updatetime."</td><td>2</td></tr>";}
				if(!empty($child2_pagekey)){
					$child3_res = file_get_contents(INFO_URL."&page_key=".$child2_xmlArray["Result"][$k]["PageKey"], false, stream_context_create($options));
					$child3_res = str_replace("<![CDATA[", "", $child3_res);
					$child3_res = str_replace("]]>", "", $child3_res);
					$child3_xmlObject = simplexml_load_string($child3_res);
					$child3_xmlArray = json_decode( json_encode( $child3_xmlObject ), TRUE ) ;
					for ($l=0; $l < $child3_xmlArray['@attributes']['totalResultsAvailable']; $l++){
						if(!empty($child3_xmlArray["Result"][$l]["PageKey"])){$child3_pagekey = $child3_xmlArray["Result"][$l]["PageKey"];}else{$child3_pagekey ="";}
						if(!empty($child3_xmlArray["Result"][$l]["Name"])){
							$child3_name = $child3_xmlArray["Result"][$l]["Name"];
							$child3_path = $child2_path . ":" . $child3_name;
						}else{
							$child3_name = "";
							$child3_path = "";
						}
						if(!empty($child3_xmlArray["Result"][$l]["Display"])){$child3_display = $child3_xmlArray["Result"][$l]["Display"];}else{$child3_display = "";}
						if(!empty($child3_xmlArray["Result"][$l]["HiddenPageFlag"])){$child3_hiddenpageflag = $child3_xmlArray["Result"][$l]["HiddenPageFlag"];}else{$child3_hiddenpageflag = "";}
						if(!empty($child3_xmlArray["Result"][$l]["SortOrder"])){$child3_sortorder = $child3_xmlArray["Result"][$l]["SortOrder"];}else{$child3_sortorder = "";}
						if(!empty($child3_xmlArray["Result"][$l]["UpdateTime"])){$child3_updatetime = $child3_xmlArray["Result"][$l]["UpdateTime"];}else{$child3_updatetime = "";}
						if(!empty($child3_pagekey)){echo "<tr><td>".$child2_pagekey."</td><td>".$child3_pagekey."</td><td>".$child3_path."</td><td><a href='https://store.shopping.yahoo.co.jp/".SellerID."/".$child3_pagekey.".html'>https://store.shopping.yahoo.co.jp/".SellerID."/".$child3_pagekey.".html</a></td><td>".$child3_name."</td><td>".$child3_display."</td><td>".$child3_hiddenpageflag."</td><td>".$child3_sortorder."</td><td>".$child3_updatetime."</td><td>3</td></tr>";}
						if(!empty($child3_pagekey)){
							$child4_res = file_get_contents(INFO_URL."&page_key=".$child3_xmlArray["Result"][$l]["PageKey"], false, stream_context_create($options));
							$child4_res = str_replace("<![CDATA[", "", $child4_res);
							$child4_res = str_replace("]]>", "", $child4_res);
							$child4_xmlObject = simplexml_load_string($child4_res);
							$child4_xmlArray = json_decode( json_encode( $child4_xmlObject ), TRUE ) ;
							for ($m=0; $m < $child4_xmlArray['@attributes']['totalResultsAvailable']; $m++){
								if(!empty($child4_xmlArray["Result"][$m]["PageKey"])){$child4_pagekey = $child4_xmlArray["Result"][$m]["PageKey"];}else{$child4_pagekey ="";}
								if(!empty($child4_xmlArray["Result"][$m]["Name"])){
									$child4_name = $child4_xmlArray["Result"][$m]["Name"];
									$child4_path = $child3_path . ":" . $child4_name;
								}else{
									$child4_name = "";
									$child4_path = "";
								}
								if(!empty($child4_xmlArray["Result"][$m]["Display"])){$child4_display = $child4_xmlArray["Result"][$m]["Display"];}else{$child4_display = "";}
								if(!empty($child4_xmlArray["Result"][$m]["HiddenPageFlag"])){$child4_hiddenpageflag = $child4_xmlArray["Result"][$m]["HiddenPageFlag"];}else{$child4_hiddenpageflag = "";}
								if(!empty($child4_xmlArray["Result"][$m]["SortOrder"])){$child4_sortorder = $child4_xmlArray["Result"][$m]["SortOrder"];}else{$child4_sortorder = "";}
								if(!empty($child4_xmlArray["Result"][$m]["UpdateTime"])){$child4_updatetime = $child4_xmlArray["Result"][$m]["UpdateTime"];}else{$child4_updatetime = "";}
								if(!empty($child4_pagekey)){echo "<tr><td>".$child3_pagekey."</td><td>".$child4_pagekey."</td><td>".$child4_path."</td><td><a href='https://store.shopping.yahoo.co.jp/".SellerID."/".$child4_pagekey.".html'>https://store.shopping.yahoo.co.jp/".SellerID."/".$child4_pagekey.".html</a></td><td>".$child4_name."</td><td>".$child4_display."</td><td>".$child4_hiddenpageflag."</td><td>".$child4_sortorder."</td><td>".$child4_updatetime."</td><td>4</td></tr>";}
								if(!empty($child4_pagekey)){
									$child5_res = file_get_contents(INFO_URL."&page_key=".$child4_xmlArray["Result"][$m]["PageKey"], false, stream_context_create($options));
									$child5_res = str_replace("<![CDATA[", "", $child5_res);
									$child5_res = str_replace("]]>", "", $child5_res);
									$child5_xmlObject = simplexml_load_string($child5_res);
									$child5_xmlArray = json_decode( json_encode( $child5_xmlObject ), TRUE ) ;
									for ($n=0; $n < $child5_xmlArray['@attributes']['totalResultsAvailable']; $n++){
										if(!empty($child5_xmlArray["Result"][$n]["PageKey"])){$child5_pagekey = $child5_xmlArray["Result"][$n]["PageKey"];}else{$child5_pagekey ="";}
										if(!empty($child5_xmlArray["Result"][$n]["Name"])){
											$child5_name = $child5_xmlArray["Result"][$n]["Name"];
											$child5_path = $child4_path . ":" . $child5_name;
										}else{
											$child5_name = "";
											$child5_path = "";
										}
										if(!empty($child5_xmlArray["Result"][$n]["Display"])){$child5_display = $child5_xmlArray["Result"][$n]["Display"];}else{$child5_display = "";}
										if(!empty($child5_xmlArray["Result"][$n]["HiddenPageFlag"])){$child5_hiddenpageflag = $child5_xmlArray["Result"][$n]["HiddenPageFlag"];}else{$child5_hiddenpageflag = "";}
										if(!empty($child5_xmlArray["Result"][$n]["SortOrder"])){$child5_sortorder = $child5_xmlArray["Result"][$n]["SortOrder"];}else{$child5_sortorder = "";}
										if(!empty($child5_xmlArray["Result"][$n]["UpdateTime"])){$child5_updatetime = $child5_xmlArray["Result"][$n]["UpdateTime"];}else{$child5_updatetime = "";}
										if(!empty($child5_pagekey)){echo "<tr><td>".$child4_pagekey."</td><td>".$child5_pagekey."</td><td>".$child5_path."</td><td><a href='https://store.shopping.yahoo.co.jp/".SellerID."/".$child5_pagekey.".html'>https://store.shopping.yahoo.co.jp/".SellerID."/".$child5_pagekey.".html</a></td><td>".$child5_name."</td><td>".$child5_display."</td><td>".$child5_hiddenpageflag."</td><td>".$child5_sortorder."</td><td>".$child5_updatetime."</td><td>5</td></tr>";}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
	echo '取得完了！<br />再取得する場合は↓をクリックするのじゃ！<br /><a href="login.php"><img src="https://s.yimg.jp/images/login/btn/btn_autofill_a_280.png" width="140" height="19" alt="ログインして自動入力" border="0"></a>';
?>

</table>
</body>
</html>