$(function(){
	var SLIDECOL = 2;
	var AUTOPLAY = "on" ;	
	var CAROUSELNUM = $('.carousel').length;
	var CAROUSELNUM2 = $('.carouselzoom').length;
	var delayTime = 3000 ;

	var carouObj = new Array();
	for(i=0; i < CAROUSELNUM; i++) {
		carouObj[i] = new Object();
		carouObj[i].auto = false;
		carouObj[i].items = SLIDECOL;
		$('.carousel ul').eq(i).carouFredSel(carouObj[i]);
	}
	var carouObjzoom = new Array();
	for(i=0; i < CAROUSELNUM2; i++) {
		carouObjzoom[i] = new Object();
		carouObjzoom[i].auto = false;
		carouObjzoom[i].items = 1;
		carouObjzoom[i].pagination = "#pagination0"+i;
		$('.carouselzoom ul').eq(i).carouFredSel(carouObjzoom[i]);
	}
	$('.zoom').hide();
	
	/* nextButton ------------------------------------------------ */
	$('.carousel .nextButton').click(function() {
/*		var nextNum = $('.nextButton').index(this);
*/		$('.carousel ul').trigger("next", 1);
	});
	
	/* prevButton ------------------------------------------------ */
	$('.carousel .prevButton').click(function() {
/*		var prevNum = $('.prevButton').index(this);
*/		$('.carousel ul').trigger("prev", 1);
	});
	
	
		
	/* nextButton ------------------------------------------------ */
	$('.carouselzoom .nextButton').click(function() {
		$('.carouselzoom ul').trigger("next", 1);
	});
	
	/* prevButton ------------------------------------------------ */
	$('.carouselzoom .prevButton').click(function() {
		$('.carouselzoom ul').trigger("prev", 1);
	});
	
	/* click ------------------------------------------------ */
	$('.normal .carousel li').click(function() {
		var photoFileName = $('img', this).attr('src');
		if( photoFileName.match(/images\/index_ph([0-9]+)\-([0-9]+)\-([0-9]+)\.jpg/)) {
			var num = RegExp.$3;
			num --;
		}
		var target = $(this).parent().parent().parent().parent().parent().parent();
		
		$('.normal', target).hide();
		$('.zoom', target).show();
		$('.carouselzoom ul', target).trigger("slideTo", [num, {
			fx     : "none",
		}]);

	});
	$('.closeZoom').click(function() {
		var target = $(this).parent().parent().parent().parent().parent().parent();
		$('.normal', target).show();
		$('.zoom', target).hide();
	});
	
	
	
	
	
	// 自動再生（有り無し）
	if(AUTOPLAY == 'on'){
		function timerStart(){
			setTimerCal = setInterval(function(){
				$('.carousel ul').trigger("next", 1);
			},delayTime );
		};
	}
	
	timerStart();
	
	$('.carousel ul li, .carousel .nextButton, .carousel .prevButton').hover(function() {
		clearInterval(setTimerCal);
	},function() {
		timerStart();
	});
	
	$(".recruitPop .inline").click(function() {
		clearInterval(setTimerCal);
		timerStart();
	});
	
	$(".inline").click(function() {
		$('.normal').show();
		$('.zoom').hide();
	});
});
