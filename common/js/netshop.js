
/*	faq
*****************************************/
$(function () {
	$('.faqFrame01 .list03 dd').hide();
	$('.faqFrame01 .list03 dt').css("padding-bottom","1px");
	
	$(".faqFrame01 .list03").click(function( ) {
		if($(this).hasClass("open")) {
			$('dd', this).hide();
			$(this).removeClass("open");
			$('dt', this).css("padding-bottom","1px");
		} else {
			$('dd', this).show();
			$(this).addClass("open");
			$('dt', this).css("padding-bottom","15px");
		}
	});
});


