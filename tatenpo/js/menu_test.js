$(function(){
$("#openMenu").click(function(){
$("#layerMenu").height(window.innerHeight );
$("#layerMenu").fadeIn("fast");
});
$("#closeMenu").click(function(){
$("#layerMenu").fadeOut("fast");
});
});