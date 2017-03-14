
var WINDOW_WIDTH = window.innerWidth;
var WINDOW_HEIGHT = window.innerHeight;

// loading
$("body").append('<div id="loading" style="width: 100%;height: ' + WINDOW_HEIGHT + 'px; background-color: rgba(0,0,0,0.8);position: fixed;top:0;left: 0;z-index: 999;" ><img src="img/loading.gif"><p style="color:#fff; text-align: center;">正在加载...</p></div>');
$("#loading img").css( {
		"width": "50px",
		"display": "block",
		"margin": "0 auto",
		"margin-bottom":"10px",
		"background-color":"rgba(0,0,0,0)",
} );
$("#loading").css( "padding-top", ( $(window).height() - $("#loading img").height() )/2 + "px" );

$(document).ready(function() {

	$("#page").css({
		'width' : WINDOW_WIDTH + 'px' ,
		'height' : WINDOW_HEIGHT + 'px'
	});

	$("#loading").fadeOut('500');


	$(".option-page ul").height( $(".option-page ul").width() );

	$(".option-page").css( "padding-top", ( WINDOW_HEIGHT - $(".option-page .content").height() )/3 + 'px' );
	$(".option-page .content").height( ( WINDOW_HEIGHT - ( WINDOW_HEIGHT - $(".option-page .content").height() )/3 ) - 30 + 'px' )

	//寝室报修 类型控制

	$("#mold").change(function(event) {
		
		for( var i = 0 ; i < $("#qes-cont select").length ; i++ )
		{
			$("#qes-cont select").eq(i).hide();
		}

		$("#qes-cont select").eq( $("#mold").val() - 1 ).show();
	});

});