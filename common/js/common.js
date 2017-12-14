
/*	hover
*****************************************/
$(function () {
	$('.hover').css('background-color','#ffffff');
	$('.hover img').hover(function(){
		$(this).css('opacity','0.6');
	},function(){
		$(this).css('opacity','1.0');
	});
	$('.hover input[type=image]').hover(function(){
		$(this).css('opacity','0.6');
	},function(){
		$(this).css('opacity','1.0');
	});
	$('.hover img').each(function(){
		var a = $(this);
		var img = a.width();
		$(a).closest('.hover').width(img);
	});
});


/*	agree
*****************************************/
$(function () {

	var agree = $('#agree');
	if(agree !== undefined){
		if(agree.is(":checked")) {
			$("#agreeON").show();
			$("#agreeOFF").hide();
		} else {
			$("#agreeON").hide();
			$("#agreeOFF").show();
		}
	}

	$('#agree').click(function() {
		if($(this).is(":checked")) {
			$("#agreeOFF").hide();
			$("#agreeON").show();
		} else {
			$("#agreeOFF").show();
			$("#agreeON").hide();
		}
	});
});



/*	rollover
*****************************************/
$(function(){
	$('.rollover').hover(function(){
		var str = $(this).attr('src');
		if(str.indexOf('on.')==-1){
			str = str.replace(/\.\w+$/, 'on' + '$&');
			$(this).attr('src',str);
		}
	},function(){
		var str = $(this).attr('src');
		str = str.replace('on.','.');
		$(this).attr('src',str);
	});
	var _imgArray = new Array();
	for(var i = 0 ; i < $('.rollover').length ; i++){
		var _str = $('.rollover').eq(i).attr('src');
		_str= _str.replace('.','on.');
		_imgArray.push(_str);
	}
	function loopImageLoader(i){
		var image1 = new Image();
		image1.src = _imgArray[i];
		image1.onload = function(){
			i++;
			if(_imgArray.length != i){
				//alert('nextnum : ' + i);//debug
				loopImageLoader(i);
			}
		}
	}
});




/* 検索フィールドフォーカス */

$(function() {
	$("#srchtxt").focus(function() {
		$(this).parent().addClass("focus")
		$(this).addClass("focus")
	});
	$("#srchtxt").blur(function() {
		$(this).parent().removeClass("focus")
		$(this).removeClass("focus")
	});
});



$(function() {
	//ページトップボタン
	//スクロール1px以上でフェイドイン
	//スクロール0pxでフェイドアウト
	
	var topBtn = $('.pagetop_FIX a');	
	topBtn.hide();
	$(window).scroll(function () {
		if ($(this).scrollTop() > 1) {
			topBtn.fadeIn();
		} else {
			topBtn.fadeOut();
		}
	});
	
	//ページトップボタンロールオーバー
	$('.pagetop_FIX a img').hover(function() {
		var str = $(this).attr('src');
		if(str.indexOf('on.')==-1){
			str = str.replace(/\.\w+$/, 'on' + '$&');
			$(this).attr('src',str).fadeTo(0, 0.3).fadeTo("slow", 1);
		}
	},function(){
		var str = $(this).attr('src');
		str = str.replace('on.','.');
		$(this).attr('src',str);
	});
	
	//ページトップボタンの位置初期設定
	
	if( ! ($(window).width() > 1160) ) {
		topBtn.css('right', "10px" );
		topBtn.css('margin-left', "none" );
	}
	
	//ウィンドウリサイズ時ページトップボタンの位置設定
	$(window).resize(function(){
		if( $(window).width() > 1160 ) {
			topBtn.css('right', "auto" );
		} else {
			topBtn.css('right', "10px" );
		}
	});

});



/*	smooth scroll
*****************************************/
$(function(){
    $('a[href^="#"]:not(.inline)').click(function(event) {
        var id = $(this).attr("href");
        var offset = 0;
        var target = $(id).offset().top - offset;
        $('html, body').animate({scrollTop:target}, 500);
        event.preventDefault();
        return false;
    });
});



$(function(){
	$(".solutionBox01Inner01 .col01, .solutionBox01Inner01 .col02, .solutionBox01Inner01 .col03").click(function( ) {
		if( $(this).find("a").attr('href') ) {
			window.location = $(this).find("a").attr('href');
		}
	});
});

$(function(){
	$(".topList01 li").click(function( ) {
		if( $(this).find("a").attr('href') ) {
			window.location = $(this).find("a").attr('href');
		}
	});
});






/*	Global Navi
*****************************************/
$.preloadImages = function()
{
	for(var i = 0; i<arguments.length; i++)
	{
		jQuery("<img>").attr("src", arguments[i]);
	}
};
$.preloadImages("/common/images/nav01on.png", "/common/images/nav02on.png", "/common/images/nav03on.png", "/common/images/nav04on.png", "/common/images/nav_bg01laston.png", "/common/images/nav_bg01on.png", "/common/images/nav_bg03laston.png", "/common/images/nav_bg03on.png", "/common/images/nav_bg05laston.png", "/common/images/nav_bg05on.png");

var imgOnTarget;
var imgOffTarget01;
var imgOffTarget02;
var imgOffTarget03;
var imgOffTarget04;
var imgOffSrc = "";

var menuCloseflag02 = 0;
var menuCloseflag03 = 0;
var menuCloseflag04 = 0;
var menuback = 0;
var HIDE_WAIT_TIME = 100;
var MOVE_TIME = 100;
var SHOW_WAIT_TIME = 200;


$(function(){

	$("#globalNav li:nth-child(2) li:last").addClass("last");
	$("#globalNav li:nth-child(3) li:last").addClass("last");
	$("#globalNav li:nth-child(4) li:last").addClass("last");

	$('#globalNav li:nth-child(1) img:not(#globalNav li ul li img)').hover(function(){
		imgOnTarget = $(this);
		gnaviOn();
	},function(){
		imgOffTarget01 = $(this);
		gnaviOff(1);
	});
	$('#globalNav li:nth-child(2) img:not(#globalNav li ul li img)').hover(function(){
		menuCloseflag02 = 0;
		imgOnTarget = $(this);
		gnaviOn();
	},function(){
		imgOffTarget02 = $(this);
		gnaviOff(2);
	});
	$('#globalNav li:nth-child(3) img:not(#globalNav li ul li img)').hover(function(){
		menuCloseflag03 = 0;
		imgOnTarget = $(this);
		gnaviOn();
	},function(){
		imgOffTarget03 = $(this);
		gnaviOff(3);
	});
	$('#globalNav li:nth-child(4) img:not(#globalNav li ul li img)').hover(function(){
		menuCloseflag04 = 0;
		imgOnTarget = $(this);
		gnaviOn();
	},function(){
		imgOffTarget04 = $(this);
		gnaviOff(4);
	});
});


//ドロップダウンメニュー
$(function(){
	if($(location).attr('href').match(/\/solution\//)) {
		$("#globalNav li:nth-child(2):not('#globalNav li li')").addClass("on");
		imgOnTarget = $("#globalNav li:nth-child(2) img:not('#globalNav li li img')");
		gnaviOn();
	}
	if($(location).attr('href').match(/\/company\//)) {
		$("#globalNav li:nth-child(3):not('#globalNav li li')").addClass("on");
		imgOnTarget = $("#globalNav li:nth-child(3) img:not('#globalNav li li img')");
		gnaviOn();
	}
	if($(location).attr('href').match(/\/recruit\//)) {
		$("#globalNav li:nth-child(4):not('#globalNav li li')").addClass("on");
		imgOnTarget = $("#globalNav li:nth-child(4) img:not('#globalNav li li img')");
		gnaviOn();
	}


	$("#globalNav li .child01").hide();
	$("#globalNav li .child02").hide();
	$("#globalNav li .child03").hide();
	
	/* tab 2 -------------------------------------------- */
	$("#globalNav li:nth-child(2):not('#globalNav li li')").hover(function() {
		menuCloseflag02 = 0;
		show_dropdownmenu(2);
	},function() {
		menuCloseflag02 = 1;
		setTimeout(function(){
			hide_dropdownmenu(2);
			gnaviOff(2);
		}, HIDE_WAIT_TIME);
	});
	
	$("#globalNav li:nth-child(2) li").hover(function() {
		menuCloseflag02 = 0;
		show_dropdownmenu(2);
	},function() {
		menuCloseflag02 = 1;
		setTimeout(function(){
			hide_dropdownmenu(2);
			gnaviOff(2);
		}, HIDE_WAIT_TIME);
	});
	
	
	/* tab 3 -------------------------------------------- */
	$("#globalNav li:nth-child(3):not('#globalNav li li')").hover(function() {
		menuCloseflag03 = 0;
		show_dropdownmenu(3);
	},function() {
		menuCloseflag03 = 1;
		setTimeout(function(){
			hide_dropdownmenu(3);
			gnaviOff(3);
		}, HIDE_WAIT_TIME);
	});
	
	$("#globalNav li:nth-child(3) li").hover(function() {
		menuCloseflag03 = 0;
		show_dropdownmenu(3);
	},function() {
		menuCloseflag03 = 1;
		setTimeout(function(){
			hide_dropdownmenu(3);
			gnaviOff(3);
		}, HIDE_WAIT_TIME);
	});
	
	
	/* tab 4 -------------------------------------------- */
	$("#globalNav li:nth-child(4):not('#globalNav li li')").hover(function() {
		menuCloseflag04 = 0;
		show_dropdownmenu(4);
	},function() {
		menuCloseflag04 = 1;
		setTimeout(function(){
			hide_dropdownmenu(4);
			gnaviOff(4);
		}, HIDE_WAIT_TIME);
	});
	
	$("#globalNav li:nth-child(4) li").hover(function() {
		menuCloseflag04 = 0;
		show_dropdownmenu(4);
	},function() {
		menuCloseflag04 = 1;
		setTimeout(function(){
			hide_dropdownmenu(4);
			gnaviOff(4);
		}, HIDE_WAIT_TIME);
	});
	
	
});

function show_dropdownmenu(target) {
	setTimeout(function(){
	switch (target) {
		case 2: if( ! menuCloseflag02 ) {
			$("#globalNav .child01").show();
			$("#globalNav .child01").animate({ 
				top: "36px",
				opacity:"1"
			}, MOVE_TIME );
		} break;
		case 3: if( ! menuCloseflag03 ) {
			$("#globalNav .child02").show();
			$("#globalNav .child02").animate({ 
				top: "36px",
				opacity:"1"
			}, MOVE_TIME );
		} break;
		case 4: if( ! menuCloseflag04 ) {
			$("#globalNav .child03").show();
			$("#globalNav .child03").animate({ 
				top: "36px",
				opacity:"1"
			}, MOVE_TIME );
		} break;
	}
	}, SHOW_WAIT_TIME);
}


function hide_dropdownmenu(target) {
	switch (target) {
		case 2: if( menuCloseflag02 ) {
			$("#globalNav .child01").animate({
				top: "51px",
				opacity:"0"
			}, MOVE_TIME );
			$("#globalNav .child01").hide(MOVE_TIME);
  
		} break;
		case 3: if( menuCloseflag03 ) {
			$("#globalNav .child02").animate({ 
				top: "51px",
				opacity:"0"
			}, MOVE_TIME );
			$("#globalNav .child02").hide(MOVE_TIME);
		} break;
		case 4: if( menuCloseflag04 ) {
			$("#globalNav .child03").animate({ 
				top: "51px",
				opacity:"0"
			}, MOVE_TIME );
			$("#globalNav .child03").hide(MOVE_TIME);
		} break;
	}
}


function gnaviOff(target) {
	switch (target) {
		case 1: 
			var str = imgOffTarget01.attr('src');
			str = str.replace('on.','.');
			imgOffTarget01.attr('src',str);
		break;
		case 2: 
			if( ! $("#globalNav li:nth-child(2)").hasClass("on") ) {
				if( menuCloseflag02 ) {
					var str = imgOffTarget02.attr('src');
					str = str.replace('on.','.');
					imgOffTarget02.attr('src',str);
				}
			}
			break;
		case 3:
			if( ! $("#globalNav li:nth-child(3)").hasClass("on") ) {
				if( menuCloseflag03 ) {
					var str = imgOffTarget03.attr('src');
					str = str.replace('on.','.');
					imgOffTarget03.attr('src',str);
				}
			} break;
		case 4: 
			if( ! $("#globalNav li:nth-child(4)").hasClass("on") ) {
				if( menuCloseflag04 ) {
					var str = imgOffTarget04.attr('src');
					str = str.replace('on.','.');
					imgOffTarget04.attr('src',str);
				}
			} break;
	}
}

function gnaviOn() {
	var str = imgOnTarget.attr('src');
	if(str.indexOf('on.')==-1){
		str = str.replace(/\.\w+$/, 'on' + '$&');
		imgOnTarget.attr('src',str).fadeTo(0, 0.5).fadeTo("fast", 1);
	}
}


