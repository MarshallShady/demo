
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

window.onload = function()
{

	$("#loading").fadeOut('500');

	$("#page").css({
		'width' : WINDOW_WIDTH + 'px' ,
		'height' : WINDOW_HEIGHT + 'px'
	});
	$("#alert").css({
		'left' : ( WINDOW_WIDTH - $("#alert")[0].offsetWidth )/2 + 'px',
		'top'  : ( WINDOW_HEIGHT - $("#alert")[0].offsetHeight )/2 + 'px',
		'display' : 'none',
	});
	$("td").width(WINDOW_WIDTH/8);

	var tdWidth = WINDOW_WIDTH/8 ,
		tdHeight = $("#td1")[0].offsetHeight,
		thHeight = $("#th1")[0].offsetHeight + 1,
		thWidth = tdWidth;

		//data为后台发过来的数据格式
	// var data = [
	// 	{
	// 		whatDay : 1 ,
	// 		whichSection : 1 ,
	// 		howLong : 2 ,
	// 		data : "中国近代史是什么呢 就是很厉害1"
	// 	},
	// ];

	
	oShow = {

		colorArr : [
			'rgba(97, 189, 230,0.8)', 'rgba(134, 167, 234,0.8)','rgba(165, 156, 219,0.8)', 
			'rgba(118, 190,172,0.8)','rgba(147, 206, 97,0.8)' , 'rgba(217,144,181,0.8)', 
			'rgba(97, 189, 230,0.8)', 'rgba(238, 196, 115, 0.8)', 'rgba(138, 216, 162,0.8)', 
			'rgba(202, 165, 158,0.8)', 'rgba(222, 196, 145,0.8)','rgba(97, 189, 230,0.8)', 
			'rgba(134, 167, 234,0.8)','rgba(165, 156, 219,0.8)', 'rgba(118, 190,172,0.8)',
			'rgba(147, 206, 97,0.8)' , 'rgba(217,144,181,0.8)', 'rgba(97, 189, 230,0.8)', 
			'rgba(238, 196, 115, 0.8)', 'rgba(138, 216, 162,0.8)', 'rgba(202, 165, 158,0.8)', 
			'rgba(145, 180, 215,0.8)', 'rgba(222, 196, 145,0.8)','rgba(97, 189, 230,0.8)', 
			'rgba(134, 167, 234,0.8)','rgba(165, 156, 219,0.8)', 'rgba(118, 190,172,0.8)',
			'rgba(147, 206, 97,0.8)' , 'rgba(217,144,181,0.8)', 'rgba(97, 189, 230,0.8)', 
			'rgba(238, 196, 115, 0.8)', 'rgba(138, 216, 162,0.8)', 'rgba(202, 165, 158,0.8)', 
			'rgba(222, 196, 145,0.8)','rgba(97, 189, 230,0.8)', 'rgba(134, 167, 234,0.8)',
			'rgba(165, 156, 219,0.8)', 'rgba(118, 190,172,0.8)','rgba(147, 206, 97,0.8)' , 
			'rgba(217,144,181,0.8)', 'rgba(97, 189, 230,0.8)', 'rgba(238, 196, 115, 0.8)', 
			'rgba(138, 216, 162,0.8)', 'rgba(202, 165, 158,0.8)', 'rgba(145, 180, 215,0.8)', 
			'rgba(222, 196, 145,0.8)'
		],

		init :function( data ){

			var oData = data;
			var color = '';

			for( var i in oData )
			{
				color = this.colorArr[  Math.floor( Math.random() * (this.colorArr.length - 1) ) ];
				this.addContent( 
					oData[i].whatDay , 
					oData[i].whichSection , 
					oData[i].howLong, 
					oData[i].data , 
					color 
					);
			}

			this.clickData();
		},

		addContent : function( whatDay , whichSection , howLong , data , color ){
			//whatDay:周几 ，whichSection：第几节， howLong：多长， data：课程内容， 背景色

			var oDiv = document.createElement('div');
			var oP = document.createElement('p');

			var left = ( whatDay - 1 ) * (tdWidth )  + thWidth,
				top = ( whichSection - 1 ) * tdHeight  + thHeight,
				height = howLong * tdHeight - 1,
				width = ( tdWidth - 2 );
			$(oDiv).css({

				'left'  : left + 'px',
				'top'   : top  + 'px',
				'width' : width + 'px',
				'height': height + 'px',
				'background-color' : color ,
			});

			$(oP).append( data );
			$(oDiv).append( oP );
			$("#content").append(oDiv);
			$(oDiv).css( 'padding-top' , ( height - $(oP).height() )/2 + 'px' );
			// this.alertData( oDiv );
		},

		clickData : function(){

			var that = this;
			for( var i = 0 ; i < $("#content div").length ; i++ )
			{
				$("#content div").eq(i).click(function(event) {
					
					that.alertData( this );

					event.stopPropagation();
				});
			}

			$('table').click(alertFadeOut);
			$(document).click(alertFadeOut);
			$('#alert').click(alertFadeOut);
			function alertFadeOut(event){
				
				$("#alert").fadeOut( 500 );
			}
		},

		alertData : function( obj ){

			$("#alert").html( $(obj).html() );

			$("#alert").fadeIn( 500 );

			$("#alert").css( 'padding-top' , ( WINDOW_HEIGHT*0.3 - $("#alert p").height() )/3 + 'px' );
		},
	};

	$.post("http://api.hduhelp.com/api/table.php",{ stuno : 13041738 ,password : 'ab123654789' }, postData );

	function postData(data){
	  
	    if( data == 'empty' ) 
	    {
	    	setTimeout(function(){
	    		$.post("http://api.hduhelp.com/api/table.php",{ stuno : 13934209 ,password : 'xianshi.' }, postData );
	    	}, 1000);
	    	return;
	    }
	  	  
	    var temp = eval( '[' + data + ']' ),temp1 = [];

	  

	  	for( var j in temp[0]['whatDay'] )
	  	{
	  	  temp1.push( {
	  	  	'whatDay' 	   : temp[0]['whatDay'][j] ,
	  	  	'whichSection' : temp[0]['whichSection'][j] ,
	  	  	'howLong' 	   : temp[0]['howLong'][j] ,
	  	  	'data'		   : temp[0]['data'][j] ,
	  	  } );
	  	  
	  	}
		
		oShow.init( temp1 );

		$("#loading").fadeOut( 500 );
	  
	}



};