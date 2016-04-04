;(function(){
	window.creatOrder = {
		numOrder : 0,
		status : [
			'已出单','已支付','已派送','待付款'
		],
		creatOne : function( num ){
			//添加一个订单卡片
			//添加 inData 数组厘米的地 num 个数据

			var result = '<article class="order-ele">\
			<div class="order-ele-img">\
				<div class="order-ele-padding">\
					<img src="static/data/image/1.png" alt="">\
				</div>\
			</div>\
			<div class="order-ele-article">\
				<div class="order-ele-padding">\
					<p class="order-ele-price">￥'+ inData[num].cost +'<span>'+ creatOrder.status[ inData[num].status ] +'</span></p>\
					<div class="order-ele-data">\
						<p class="order-ele-name">'+ inData[num].name +'</p>\
						<p class="order-ele-date">'+ inData[num].time +'安排配送</p>\
					</div>\
					<div class="order-ele-action">\
						'+ creatBtn() +'\
					</div>\
				</div>\
			</div>\
		</article>';
			return result;
			function creatBtn( status ){
				var result = '',
					data = inData[num].operation;

				for (var i = data.length-1; i >= 0; i--) {
					var emphasize =  data[i].emphasize? '':'order-btn-bg';
					result += '<a href="'+ data[i].href +'" class="gw-btn order-ele-btn '+ emphasize +'">'+ data[i].name +'</a>';
				}
				return result;
			}
		},
		creats : function( num ){//num：一次显示几条
			var result = '';
			for( var i = this.numOrder,len = this.numOrder + num; i < len; i++,this.numOrder++ ){
				if( i >= inData.length ){
					$(".more").hide();
					return ;
				}
				result += creatOrder.creatOne( i );
			}
			$('.main-section').append( result );
			if( this.numOrder >= inData.length ){
				$(".more").hide();
				return ;
			}
		},
		init : function( numInit, addNum ){
			//numInit 初始化订单卡片数
			//addNum 查看更多添加的卡片数
			creatOrder.creats( numInit );
			$(".more").tap(function(){
				creatOrder.creats( addNum );
				confirmMe('order-ele-btn');
			});
			confirmMe('order-ele-btn');


		}
	};
	confirmMe('order-ele-btn');
	function confirmMe( elmClassName ){
		$('.' + elmClassName).click(function(){
			var r = confirm( '是否' + $(this).html() + '?' );
			if(!r){
				return false;
			}
			return true;
		});
	}
}());