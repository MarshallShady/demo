var WINDOW_WIDTH = window.innerWidth,
	WINDOW_HEIGHT = window.innerHeight;

// loading
$("body").append('<div id="loading" style="width: 100%;height: ' + WINDOW_HEIGHT + 'px; background-color: rgba(0,0,0,0.8);position: fixed;top:0;left: 0;z-index: 999;" ><img src="img/loading.gif"><p style="color:#fff; text-align: center; font-size:10px;">正在加载...</p></div>');
$("#loading img").css( {
		"width": "50px",
		"display": "block",
		"margin": "0 auto",
		"margin-bottom":"10px",
		"background-color":"rgba(0,0,0,0)",
} );
$("#loading").css( "padding-top", ( $(window).height() - $("#loading img").height() )/2 + "px" );

window.onload = function()
{
	$("#loading").fadeOut();


	//电话本具体信息
	$("#phone .option p").click(function(event) {
		
		$(this).next().slideToggle(300);
	});
};