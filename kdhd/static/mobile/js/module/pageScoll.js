

;

function pageScoll( page, initSpeed ){

	//page 为需要滚动条运动的dom元素

	//initSpeed为要滚动的速度 1到10

	var oPage = page;

	var iStartTop = 0,

		iStartTime = 0,

		iChendTop = 0,

		iChendTime = 0,

		iScollSpeed = 0,

		speed = initSpeed/10;

	oPage.ontouchstart = function(ev)

	{

		var oEvent=ev||event;

		var singleTouch = oEvent.changedTouches[0];

		

		iStartTop = singleTouch.pageY;



		var oDate = new Date();

		iStartTime = oDate.getTime();



		iScollSpeed = 0;



		

	};

	oPage.ontouchend = function(ev)

	{

		var oEvent=ev||event;

		var singleTouch = oEvent.changedTouches[0];

		

		iChendTop = singleTouch.pageY;



		var oDate = new Date();

		iChendTime = oDate.getTime();



		iScollSpeed = ( iChendTop - iStartTop ) / ( ( iChendTime - iStartTime ) / 30 ) ;

		iChendTime = 0;

		iStartTime = 0;

		iChendTime = 0;

		iStartTime = 0;

		if( oPage.timer )

		{

			clearInterval( oPage.timer );

		}

		if( iScollSpeed > 0 )

		{



			oPage.timer = setInterval(function(){

				iScollSpeed -= speed;

				if( iScollSpeed <= 0 )

				{

					clearInterval( oPage.timer );

				}

				$( oPage ).scrollTop( $( oPage ).scrollTop() - iScollSpeed );

			},1);

		}

		else if( iScollSpeed < 0 )

		{

			oPage.timer = setInterval(function(){

				iScollSpeed += speed;

				if( iScollSpeed >= 0 )

				{

					clearInterval( oPage.timer );

				}

				$( oPage ).scrollTop( $( oPage ).scrollTop() - iScollSpeed );

			},1);

		}

	};

}