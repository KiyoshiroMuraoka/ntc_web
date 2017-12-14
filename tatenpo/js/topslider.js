$(function(){
	var slideCurrent = 0;
	var slideWidth =880;
	var slideNum =parseInt($(".slide").length);
	var slideboxWidth = slideNum*880;
	//var slideboxWidth = parseInt(slideWidth) * parseInt(slideNum);
	var imglist = new Array(slideNum);
	var i;
	//for (i=0;i<slideNum;i++){//imglistの箱に画像のタグを入れる
		//slideboxWidth = slideboxWidth + $("#slide"+[i]).innerWidth();
	//};
	console.log(slideboxWidth);
	$(".sliderbox").css({"width":slideboxWidth});
	for (i=0;i<=slideNum;i++){//imglistの箱に画像のタグを入れる
		imglist[i]= $("#slide" + i).html();
	};
	$(".sliderbox").empty(); //sliderboxの中身を消す
	$(".sliderbox").append("<div class='slide'>"+ imglist[slideNum-2] +"</div>");//sliderboxの中身の左側を作る
	$(".sliderbox").append("<div class='slide'>"+ imglist[slideNum-1] +"</div>");
	for (i=0;i<slideNum-2;i++){ 
		$(".sliderbox").append("<div class='slide' id='slide" + i + "'>"+ imglist[i] +"</div>");//残りの中身を作る
	}
	for (i=0;i<slideNum;i++){ //●のナビを作る
		var windowWidth = $(window).width();
		$(".slidebtn").append("<span id='slidebtn"+[i]+"'>●</span>");
		if(windowWidth < 1080){
		$("#slidebtn"+[i]).css({"font-size":"200%"});
		};
	};
	var btnWidth = $(".slidebtn > span:first").innerWidth() * slideNum;//●全体の幅を取得してセンターに
	var btnmargin = (slideWidth-btnWidth)/2;
	$(".slidebtn").css({"margin-left": btnmargin});
	$("#slidebtn0").css({"color":"#cccccc"});//●の1つ目をグレーに
	var n=0;
	var flag=0;//ボタン移動後に自動ループ処理が入らないようにするためのもの
	$(".slidebtn > span").click(function(){
		var btnid = $(this).attr("id");//●のID部分取得
		var btnidno = btnid.replace(/[^0-9]/g,"");//●のIDの番号を取得
		console.log("クリック btnidno:"+btnidno+" slideCurrent:"+slideCurrent);
		var loop = setInterval(function(){
			if(btnidno == slideCurrent){//ボタンと画像番号が一緒ならループを止める
				clearInterval(loop);
				clearInterval(automove);
				console.log("停止 btnidno:"+btnidno+" slideCurrent:"+slideCurrent);
			}else{
				clearInterval(automove);
				if(flag==0){
					flag=1
					$("#slidebtn"+slideCurrent).css({"color":"#000000"});//●を黒に
					slideCurrent++;
					if(slideCurrent >= slideNum){
						slideCurrent = 0;
					};
					var imgid = $(".sliderbox > div").last().attr("id");//最後の画像のIDを取得
					console.log("自動終");
					var imgno = imgid.replace(/[^0-9]/g,"");
					var makeimgid = parseInt(imgno)+1
					if(makeimgid >= slideNum){
						makeimgid = 0;
					};
					$(".sliderbox").append("<div class='slide' id='slide" + makeimgid + "'>"+ imglist[makeimgid]  +"</div>");//最後尾に画像作成
					$(".sliderbox > div").first().animate(//先頭を削除
						{width: "0px"},200,function()
						{$(".sliderbox > div").first().remove();
						$("#slidebtn"+slideCurrent).css({"color":"#cccccc"});//●をグレーに
						flag = 0;}
					);
				}else{
				};
			};
		},1);
	});
	$(".sliderbox").css({"width":slideboxWidth});
		var automove = setInterval(function(){ //自動ループ
		if(flag == 0){
			flag=2;
			var nowslide = $('.slide').length;
			$("#slidebtn"+slideCurrent).css({"color":"#000000"});//●を黒に
			slideCurrent++;
			if(slideCurrent >= slideNum){
				slideCurrent = 0;
			};
			var imgid = $(".sliderbox > div").last().attr("id");//最後の画像のIDを取得
			console.log("自動ループ"+ slideboxWidth);
			var imgno = imgid.replace(/[^0-9]/g,"");
			var makeimgid = parseInt(imgno)+1
			if(makeimgid >= slideNum){
				makeimgid = 0;
			};
			$(".sliderbox").append("<div class='slide' id='slide" + makeimgid + "'>"+ imglist[makeimgid]  +"</div>");//最後尾に画像作成
			$(".sliderbox > div").first().animate(//先頭を削除
				{width: "0px"},1000,function()
				{$(".sliderbox > div").first().remove();flag=0;}
			);
			$("#slidebtn"+slideCurrent).css({"color":"#cccccc"});//●をグレーに
		};
	},2500);
	leftyose();
	slidecenter();
	$(window).resize(leftyose);
	$(window).resize(slidecenter);
	function leftyose(){
		var windowWidth = $(window).width();
		var start = -(((windowWidth - 1080)/2)+200);
		if(windowWidth<1080){
			start=0
		};
		$(".slider").css({"left":start,"width":windowWidth});
	};
	function slidecenter(){
		var windowWidth = $(window).width();
		console.log(windowWidth);
		var margin = -(slideWidth-(((windowWidth-1080)/2+198)%windowWidth)+slideWidth);
		if(windowWidth<1080){
			margin=-(slideWidth*2)+((windowWidth - slideWidth)/2);
		}
		$(".sliderbox").css({"left":margin});
	};
});