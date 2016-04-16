/*
 * 此模块是动态页面加载：当滚动条滚到到页面最底端时
 *
 * 此文件需要 jq 的支持，所以需要在 jq 之后引用
 */

;
function dynamicLoad( dispose ){

	var WINDOW_HERGTH = window.innerHeight + 20;
	var timer = null;
	var eleLen = 0;

	$(window).scroll(function(){
		var scrollTop =  $(window).scrollTop(),
			docHeight = $(document).height();

		if( scrollTop > docHeight - WINDOW_HERGTH ){
			// console.log( scrollTop );
			if( !timer ){
				timer = setTimeout(function(){
					timer = null;
					dispose();
				},300);
			}
		}
	});
}