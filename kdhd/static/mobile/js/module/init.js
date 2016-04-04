/*
 *	本文件做一些全局的初始化‘
 *	
 * 	如头部菜单等
 */

;(function(){


	//菜单初始化
	var memu = {
		onOff : false,
		height : parseInt( $(".memu-pull-down li").css('height') ) * $(".memu-pull-down li").length,
		move :	function (e){
			if( this.onOff == false ){
				$(".memu-pull-down").css({
					'height' : this.height+'px',
				});
				this.onOff = true;
			} else {
				$(".memu-pull-down").css({
					'height' : '0',
				});
				this.onOff = false;
			}
			
		}
	}
	$(".main-memu").tap(function(e){
		memu.move();
		e.stopPropagation();
	});

	$(document).tap(function(){
		if( memu.onOff == true ){
			memu.move();
		}
	});


}());