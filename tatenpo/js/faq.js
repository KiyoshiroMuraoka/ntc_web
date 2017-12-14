$(function(){
	$('.accordionLister dd').hide();
	$('.showExcerpt').on('click', function() {
		if($(this).parent().find('dd').css('display') == 'none'){
			 $(this).parent().find('dd').show();
			 $(this).parent().find('img').attr("src","../img/a.png");
		} else {
			$(this).parent().find('dd').hide();
			$(this).parent().find('img').attr("src","../img/q.png");
		}
	});
});