
var initFood = [
		[],//早上
		[],//中午
		[]//晚上
	];

$(document).ready(function(ready) {
	var food_all_amount = 0					//初始菜数
	,	$order_list = $("#order-list")		//菜单列表
	,	$orderInfo = $("#order-icon")		//弹出式菜单icon & 当前总选菜数
	,	$container = $("#container")		//获取卡片容器
	,	$canteen = $("#canteen");			//选择餐厅


	/*********骚辉**********/

	//菜品生成
	var manageFood = {

		nowType : 1,

		//当前餐厅
		nowCanteen : 1,

		//当前档口
		nowPort : 0,

		hint : [ , "每日11:00前可预订当日午饭哦", "每日16:00前可预订当日晚饭哦" ],

		moreHtml : '<div class="more"><img src="static/mobile/image/loading.gif" alt="loading"><span>全力为您加载中...</span></div>',

		init : function(){

			// 数据初始化
			manageFood.ajax({
				canteen : 0,
				cantentPort : 0,
				when : 0,
				len : 0,
				success : function(data){
					if( data.status == 1 ){
						initCanteen(data);
					}
					else if( data.status == 3 ){
						alert("您填写的地址并无指定的餐厅，请重新选择餐厅");
						location = "http://bd.koudaigognshe.com/mobile/position/index";
					}
				}
			});
			
			//餐厅和档口初始化
			function initCanteen(data){

				var canteenData = data.data.position;
				var resultCanteen = '';
				var resultCanteenPort = '';

				for( var i in canteenData ){
					resultCanteen += '<li><a href="javascript:;">'+ canteenData[i].name +'</a></li>';
					resultCanteenPort += '<div class="booth-ele"><ul>';

					for(var j in canteenData[i].port){
						resultCanteenPort += '<li data-canteen="'
						+ canteenData[i].id +'" data-canteen-port="'
						+ canteenData[i].port[j].id +'"><a href="javascript:;">'
						+ canteenData[i].port[j].name +'</a></li>';
					}
					resultCanteenPort += '</ul><div class="clear-float"></div></div>';
				}

				$("#restaurant ul").append( resultCanteen );
				$("#booth").append( resultCanteenPort );

				//切换档口
				var $restaurants = $("#restaurant a");

				for( var len = $restaurants.length; len--; ){
					(function(num){
						$($restaurants[num]).tap(function(e){
							$("#restaurant p").html( $(this).html() );
							$("#booth .booth-ele").css( 'display', 'none' );
							$( $("#booth .booth-ele")[num] ).css( 'display', 'block' );
							e.stopPropagation();
						});
					}(len));
				}
				var canPort = $("#booth a");
				for( var len = canPort.length; len--; ){
					(function(num){
						$(canPort[num]).tap(function(e){
							$("#booth p").html( $(this).html() );
							manageFood.nowCanteen = $(this).parent().attr("data-canteen");
							manageFood.nowPort = $(this).parent().attr("data-canteen-port");

							//清空之前档口已经加载的数据
							for(var i = 0; i < 3; i++ ){
								initFood[i] = [];
							}
							$("#container").html( manageFood.moreHtml );

							//切换档口请求
							manageFood.ajax({
								canteen : manageFood.nowCanteen,
								cantentPort : manageFood.nowPort,
								when : manageFood.nowType + 1,
								len : 0,
								success : function(data){

									if( $canteen.css("top") === "0px" )
										slide($canteen).toggle();

									if( data.status == 1 ){
										manageFood.upload( data.data.dish );

										//如果没有 < 10 个数据说明没有更多数据了
										if( data.data.dish.length < 10 ){
											$(".more").html("没有更多菜品了");
										}
									}
									else if( data.status == 2 ){
										//这个判断只是为了安全以免出错
										
										$(".more").html("没有更多菜品了");
									}
								}
							});

							e.stopPropagation();
						});
					}(len));
				}
			}


			//菜品初始化
			manageFood.ajax({
				canteen : 1,
				cantentPort : 0,
				when : 0,
				len : 0,
				success : function(data){
					manageFood.nowType = data.when-1;

					//初始化当前时间当前默认订餐时间的 active
					$("#tab li").eq( manageFood.nowType ).children('a').addClass( 'current-time' );

					if( data.status == 1 ){
						manageFood.upload( data.data.dish );

						//如果没有 < 10 个数据说明没有更多数据了
						if( data.data.dish.length < 10 ){
							$(".more").html("没有更多菜品了");
						}
					}
					else if( data.status == 2 ){
						//这个判断只是为了安全以免出错
						
						$(".more").html("没有更多菜品了");
					}
				}
			});

			//给 canteen 的 scroll 增加交互
			pageScoll( $("#canteen")[0], 5 );

			manageFood.upTo( manageFood.nowType );

			//早餐午餐晚餐的时段切换菜单
			$("#tab li").tap(function(){

				var flag = $(this).attr('data-flag');

				//当data-flag的值为 'q' 时说明不允许访问
				if( flag == 'q' ){
					alert( $(this).children().children('span').html() + "此时段暂未开通" );
					return false;
				}

				//判断购物车是否为空，如果不为空则清空
				if( food_all_amount > 0 ){
					var temp = clearShopping('切换时间点将会清空购物车，您确定切换吗？');
					if(!temp)return;
				}
				manageFood.upTo(flag);
				manageFood.cut( flag );
				$("#tab li a").removeClass();
				$(this).children().addClass('current-time');
				manageFood.nowType = flag;
			}); 
		},

		//菜品生成函数，一次生成一个页面上的 dom 
		//id:菜品的id
		//num：第几个菜品（早中晚分开）
		//img：菜品对应的链接
		//name：菜品名字
		//selNum：销量
		//cost：价格  imgSrc, name, sellNum, cost
		//{
		//		name : '晚餐'
		//		img : 'static/data/image/1.png',
		//		sellNum : 1024,
		//		cost : 12.5
		//},
		creatFood : function( data ){
			return '<div class="card food row"  data-number="'+ data.dish_id +'">\
			<img class="card-object	food-image  col-7" src="'+ data.image +'" >\
			<div class="card-text col-5"><ul class="food-info card-middle card-right large">\
			<li class="food-name icon-after" data-icon="HOT">'+ data.name +'</li>\
			<li class="food-components">'+ data.content +'</li>\
			<li class="food-sold icon-before" data-icon="店">'+ data.from +'</li>\
			<li class="food-sold icon-before" data-icon="售">'+ data.sellnum +'份</li>\
			<li class="food-unitprice warning">￥'+ data.money/100 +'</li></ul>\
			</div><div class="card-box food-box"></div><div class="food-icon-group">\
			<div class="food-icon icon-plus-minus hidden minus" data-icon="-"></div>\
			<span class="food-amount hidden">0</span>\
			<div class="food-icon icon-plus-minus plus" data-icon="+"></div>\
			</div></div>';
		},

		//electTime：选择早上中午晚上。0：早上，1：中午，2：晚上
		cut : function( electTime ){

			$("#container").html( manageFood.moreHtml );

			//如果数据为空则发起请求
			if( initFood[electTime].length == 0 ){
				manageFood.ajax({
					when : electTime + 1,
					canteen : manageFood.nowCanteen,
					cantentPort : manageFood.nowPort,
					len : 0,
					success : function(data){
						if( data.status == 1 ){
							manageFood.upload( data.data.dish );
							//如果没有 < 10 个数据说明没有更多数据了
							if( data.data.dish.length < 10 ){
								$(".more").html("没有更多菜品了");
							}
						}
						else if( data.status == 2 ){
							//这个判断只是为了安全以免出错
							
							$(".more").html("没有更多菜品了");
						}
					}
				});
				return;
			}
			manageFood.append( initFood[electTime] );
		},

		//添加懂内容到可视区
		//data：对应添加的数据：早餐、午餐或晚餐的数组
		//start：为从data的第几个元素开始添加，也就是页面已有几个元素
		append : function( data ){
			var result = '';
			for( var i = 0, len = data.length ; i < len; i++ ){
				result += manageFood.creatFood( data[i] );
			}
			$("#container").append( result );
			$("#container").append( $(".more")[0] );
		},

		//用来把后台获取到的数据添加到食物数组
		upload : function ( data ){
			initFood[ manageFood.nowType ] = initFood[ manageFood.nowType ].concat(data);
			manageFood.append( data );
		},

		//首页提醒文字
		upTo : function ( flag ){
			$("#up_to").html( manageFood.hint[flag] );
		},

		//想服务器请求数据
		ajax : function (obj){
			$.ajax({
				type : "get",
				url : "http://bd.koudaigongshe.com/mobile/apishow/index/when/"
				+ obj.when +"/canteen/"
				+ obj.canteen +"/canteen_port/"
				+ obj.cantentPort +"/len/"
				+ obj.len,
				dataType : "json",
				success : obj.success
			});

		}

	};

	//数据自动更新
	dynamicLoad(function(){
		manageFood.ajax({
			when : manageFood.nowType + 1,
			canteen : manageFood.nowCanteen,
			cantentPort : manageFood.nowPort,
			len : initFood[ manageFood.nowType ].length,
			success : function(data){
				if( data.status == 1 ){
					manageFood.upload( data.data.dish );
				}
				else if( data.status == 2 ){
					$(".more").html( data.message );
				}
			}
		});
	});	

	/*********END骚辉**********/

	$("#header").tap(function () {		//餐厅触发滑动
		slide($canteen).toggle();
	});

	/********骚辉更改***********/
	(function(){

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
		$("#memu").tap(function(e){
			memu.move();
			if( $( "#canteen" ).css('top') != '0px' ){
				e.stopPropagation();
			}
		});
		$(".memu-pull-down").tap(function(e){
			e.stopPropagation();
		});
		$(document).tap(function(){
			if( memu.onOff == true && $( "#canteen" ).css('top') == '0px' ){
				memu.move();
			}
		});
		manageFood.init();
	}());

	/**********END骚辉*****************/

	$("#order-icon").tap(function (ev) {	//购物车图标触发滑动
		var ev = ev || event;
		ev.preventDefault();
		slide($order_list).toggle();
	});

	$("#order-list-clear").tap(function(){
		clearShopping('您确定清空购物车吗？');
	});

	$("#order-enter").click(function () {
		var $order_input = $("#order-input")
		,	order = [];			//弹出式菜单————获取所有已选菜式的总价
		$order_list.find(".order-list-item").each(function () {
			order.push({
				amount : parseInt($(this).find(".item-food-amount").text()),
				id : $(this).attr("data-number")
			});
		});

		var result = {
			type :   manageFood.nowType + 1,
			data : order
		};

		var strResult = JSON.stringify(result);
		$order_input.val( strResult );
		return true;
	});
	$(document).on("tap", ".icon-plus-minus", function (ev) {
		var $that = $(this)
		,	$food_est = $that.closest(".food");
		var	type = $that.attr("data-icon");					//弹出式菜单————获取icon类型 用来判断+-号
		var $food = findFood($food_est);					//获取符合条件的菜式卡片和菜单条目

		if ($food.length > 1) {
			updateFood(type, $food);
		} else {
			if (type === "+")
				createItemDD($food_est);
		}
		updateOrderIcon();
		slide($order_list).update();
		coutTotal();
	});

	function clearShopping( msg ) {	//清空购物车
		var r = confirm( msg );
		if (!r) return false;

		$(".minus").addClass('hidden');
		$(".food-amount").addClass('hidden');

		if($order_list.css('top') != '0px'){

			slide($order_list).toggle();

		}

		$order_list.find(".food").remove();
		$container.find(".food-amount").html("0");

		food_all_amount = 0;

		updateOrderIcon();

		coutTotal();
		return true;
	}

	function createItemDD (food) {
		var	$food_name = food.find(".food-name")	
		,	$food_unitprice = food.find(".food-unitprice")
		,	$food_minus = food.find(".minus")
		,	$food_amount = food.find(".food-amount");

		var	food_number = food.attr("data-number")
		,	food_unitprice = $food_unitprice.text()
		,	food_name = $food_name.text();
		var html = '<dd class="order-list-item food" data-number="' + food_number + '"data-unitprice="' + food_unitprice + '">'
				 + '<span class="food-name">' + food_name + '</span>'
				 + '<div  class="item-food-icon icon-plus-minus float-right plus" data-icon="+"></div>'
				 + '<span class="item-food-amount float-right">' + 1 + '</span>'
				 + '<div  class="item-food-icon icon-plus-minus float-right minus" data-icon="-"></div>'
				 + '<span class="item-food-total float-right" >' + food_unitprice + '</span>'
				 + '</dd>';
		food_all_amount++;
		$food_minus.removeClass("hidden");
		$food_amount.removeClass("hidden");
		$food_amount.html("1");
		$("#order-list-title").after(html); //弹出式菜单标题
	}
	function updateOrderIcon () {
		$orderInfo.attr("data-icon",food_all_amount);		//更新icon总菜式数量
	}
	function updateFood (type, food) {
		var	$food_amount = food.find(".food-amount")				//卡片菜式显示的数量-对象获取
		,	$item_food_total = food.find(".item-food-total")		//弹出式菜单-当前选取菜式的总价-对象获取
		,	$item_food_amount = food.find(".item-food-amount")		//弹出式菜单-当前选取菜式的数量-对象获取
		var amount = parseInt($item_food_amount.text());
		if (type === "+") {
			amount++;
			food_all_amount++;
		} else if (type === "-") {
			if (amount === 1) {
				food.filter(".order-list-item").remove();
				var $card_food = food.filter(".card");
				$card_food.find(".minus").addClass("hidden");
				$card_food.find(".food-amount").addClass("hidden");
				slide($order_list).update();
			}
			amount--;
			food_all_amount--;
		} else {
			return;
		}
		var	unitprice = parseFloat(food.find(".food-unitprice").text().match(/\d+(\.\d+)?$/g));//获取单价
		var price = unitprice * amount;
		$food_amount.html(amount);							//卡片-当前选取菜式数量-更新
		$item_food_amount.html(amount);						//弹出式菜单-当前选取菜式数量-更新
		$item_food_total.html("￥" + price.toFixed(1));	//弹出式菜单-当前选取菜式总价-更新
	}
	function findFood (obj) {
		var number = obj.attr("data-number");	//当前选择的菜的编号
		var $dd = $(".food").filter(function () {
			return $(this).attr("data-number") === number;
		});	
		return $dd;
	}
	function coutTotal () {
		var $order_price = $("#order-price")
		,	total = 0;			//弹出式菜单————获取所有已选菜式的总价
		$order_list.find(".item-food-total").each(function () {
			total += parseFloat($(this).text().match(/\d+(\.\d+)?$/g));
		});
		$order_price.html("￥" + total.toFixed(1));
		var $order_enter = $("#order-enter");
		if (total >= 6) {
			$("#order-enter").html( '确认购买' );
			$order_enter.removeAttr("disabled");
			$order_enter.css("backgroundColor","#ff6550");
		} else {
			$("#order-enter").html( '6元起送' );
			$order_enter.attr("disabled","disabled");
			$order_enter.css("backgroundColor","#888");
		}
	}
	function slide(obj) {
		var $obj = obj;
		return {
			toggle : function () {
				var	height = $obj.height()
				,	dist = $obj.css("top") === '0px' ? (-height + 'px') : '0px';
				$obj.css("top", dist);
			},
			update : function () {
				var	height = $obj.height()
				,	dist = $obj.css("top") === '0px' ? '0px' : (-height + 'px');
				$obj.css("top", dist);
			}
		};
	}
});