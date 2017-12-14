$(function(){
	$("#openMenu").click(function(){
		$("#layerMenu").height( $(document).height() );
		$("#layerMenu").fadeIn("fast");
	});
	$("#closeMenu").click(function(){
		$("#layerMenu").fadeOut("fast");
	});
});