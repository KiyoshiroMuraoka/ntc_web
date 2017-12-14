// スムーズスクロール
$(function(){
	// ページ内リンクをクリックすると
	$('a[href^=#]').click(function(){
 		// スクロールスピード
 		var speed = 500;
 		// クリックしたリンク先を保存
		var href= $(this).attr("href");
		// クリックしたリンク先が#または空のときは
		var target = $(href == "#" || href == "" ? 'html' : href);
		// トップへ移動する
		var position = target.offset().top;
		// リンク先へスムーズに移動する
		$("html, body").animate({scrollTop:position}, speed, "swing");
		return false;
	});
});
// page Topフェードイン・アウト
$(function(){
	$(window).bind("scroll", function() {
		if ($(this).scrollTop() > 150) { 
			$(".pageTop").fadeIn();
		} else {
			$(".pageTop").fadeOut();
		}
		// ドキュメントの高さ
		scrollHeight = $(".main").height() + 20;
		// ウィンドウの高さ+スクロールした高さ→ 現在のトップからの位置
		scrollPosition = $(window).height() + $(window).scrollTop();
		// フッターの高さ
		footHeight = $(".footer").height();
		//ドキュメントの幅
		windowWidth = $(".main").width();

		if ( windowWidth < 1080) {
			$(".pageTop a").css({"position":"fixed","bottom": "5px"});
		} else{
			// スクロール位置がフッターまで来たら
			if ( scrollHeight <= scrollPosition) {
				// ページトップリンクをフッターに固定
				$(".pageTop a").css({"position":"absolute","bottom": "40px"});
			} else {
				// ページトップリンクを右下に固定
				$(".pageTop a").css({"position":"fixed","bottom": "0px"});
			}
		}
	});
});