
var initFood = [
		[],//早上
		[],//中午
		[]//晚上
	];
var manageFood = {};

$(document).ready(function(ready) {
	var food_all_amount = 0					//初始菜数
	,	$order_list = $("#order-list")		//菜单列表
	,	$orderInfo = $("#order-icon")		//弹出式菜单icon & 当前总选菜数
	,	$container = $("#container")		//获取卡片容器
	,	$canteen = $("#canteen");			//选择餐厅


	/*********骚辉**********/

	//菜品生成
	manageFood = {
		//存放初始化获取的data
		data : null,

		//当前时段
		nowType : 1,

		//当前餐厅
		nowCanteen : 0,

		//正在加载标志，如果正在加载 则 = false
		loadFlag : true,

		//没有数据后停止加载
		stopLoad : false,

		//当前档口
		nowPort : 0,

		//up_to 里的提示语
		hint : [ "", "", "" ],

		orderTime : [ "","","" ],

		moreHtml : '<div class="more"><img src="http://bd.koudaigongshe.com/Public/static/mobile/image/loading.gif" alt="loading"><span>全力为您加载中...</span></div>',

		init : function(){

			// 数据初始化
			manageFood.ajax({
				canteen : 0,
				cantentPort : 0,
				when : 0,
				len : 0,
				success : function(data){
					manageFood.data = data;
					console.log(data);

					manageFood.nowType = data.when - 1;
					manageFood.nowCanteen = data.canteen;
					
					manageFood.nowPort = data.canteen_port;

					initCanteen(data);
					

					//菜品初始化

					
					//初始化当前时间当前默认订餐时间的 active
					$("#tab li").eq( manageFood.nowType ).children('a').addClass( 'current-time' );

					if( data.status == 1 ){
						manageFood.upload( data.data.dish );

						// console.log( data.message );

						//如果没有 < 10 个数据说明没有更多数据了
						if( data.data.dish.length < 10 ){
							$(".more").html( "没有更多数据了" );

							//停止加载
							manageFood.stopLoad = true;

							return;
						}
					}
					else if( data.status == 2 ){
						//这个判断只是为了安全以免出错
						
						$(".more").html( data.message );

						//停止加载
						manageFood.stopLoad = true;

					} else if( data.status == 3 ){
						alert("您填写的地址并无指定的餐厅，请重新选择餐厅");
						location = "http://bd.koudaigognshe.com/mobile/position/index";
					}
					//菜品初始化结束
				}
			});


			//餐厅和档口初始化
			function initCanteen(data){

				var canteenData = data.data.position;
				var resultCanteen = '';
				var resultCanteenPort = '';

				//时间初始化
				manageFood.placeOrderTime( data );

				for( var i in canteenData ){
					resultCanteen += '<li><a href="javascript:;">'+ canteenData[i].name +'</a></li>';
					resultCanteenPort += '<div class="booth-ele"><ul>'
									    +'<li data-canteen="'
										+ canteenData[i].id +'" data-canteen-port="'
										+ '0"><a href="javascript:;">'
										+ '全部</a></li>';

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

				$("#restaurant p").html( $("#restaurant ul a").html() );
				

				$("#booth .booth-ele").eq(0).show();

				//切换餐厅
				var $restaurants = $("#restaurant a");

				for( var len = $restaurants.length; len--; ){
					(function(num){
						$($restaurants[num]).tap(function(e){
							$("#restaurant p").html( $(this).html() );
							$("#booth .booth-ele").css( 'display', 'none' );
							$( $("#booth .booth-ele")[num] ).css( 'display', 'block' );

							//判断购物车是否为空，如果不为空则清空
							if( food_all_amount > 0 ){
								var temp = clearShopping('切换餐厅将会清空购物车，您确定切换吗？');
								if(!temp)return;
							}

							e.stopPropagation();
						});
					}(len));
				}

				//切换档口
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

							//切换时段解开，停止加载的锁
							manageFood.stopLoad = false;

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

										// console.log( data.message );
										//如果没有 < 10 个数据说明没有更多数据了
										if( data.data.dish.length < 10 ){
											$(".more").html( "没有更多数据了" );

											//停止加载
											manageFood.stopLoad = true;
										}
									}
									else if( data.status == 2 ){
										//这个判断只是为了安全以免出错
										
										$(".more").html( data.message );

										//停止加载
										manageFood.stopLoad = true;
									}
								}
							});

							e.stopPropagation();
						});
					}(len));
				}
			}


			//给 canteen 的 scroll 增加交互
			$("#canteen").css( "-webkit-overflow-scrolling:", "touch" );

			manageFood.upTo( manageFood.nowType );

			//早餐午餐晚餐的时段切换菜单
			$("#tab li").tap(function(){

				var flag =  $(this).attr('data-flag') ;

				flag = parseInt( flag );
				//判断购物车是否为空，如果不为空则清空
				if( food_all_amount > 0 ){
					var temp = clearShopping('切换时间点将会清空购物车，您确定切换吗？');
					if(!temp)return;
				}
				manageFood.upTo( flag );
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
			var activity = '',
				hot = '',
				limit = 'n';
			if( data.limit_tag == 1 ){
				activity = '<div class="activity"><img src="http://bd.koudaigongshe.com/Public/static/mobile/image/limit.png" alt=""></div>';
				hot = 'icon-after'
				limit = 1;
			}
			return '<div class="card food row"  data-number="'+ data.dish_id +'">'
			+ '<img class="card-object	food-image  col-7" src="'+ data.image +'" >'
			+ activity
			+ '<div class="card-text col-5"><ul class="food-info card-middle card-right large">'
			+ '<li class="food-name '+ hot +'" data-icon="HOT">'+ data.name +'</li>'
			+ '<li class="food-components food_sales_hide ">'+ data.content +'</li>'
			+ '<li class="food-sold icon-before food_sales_hide " data-icon="店">'+ data.from +'</li>'
			+ '<li class="food-sold icon-before food_sales_hide " data-icon="售">'+ data.sellnum +'份</li>'
			+ '<li class="food-sold icon-before food_sales_show " data-icon="剩余" data-limit="'+ limit +'">'+ data.rest +'份</li>'
			+ '<li class="food-unitprice warning food_sales_hide ">￥'+ data.money/100 +'</li></ul>'
			+ '</div><div class="card-box food-box"></div><div class="food-icon-group" data-sales="daa">'
			+ '<div class="food-icon icon-plus-minus hidden minus" data-icon="-"></div>'
			+ '<span class="food-amount hidden">0</span>'
			+ '<div class="food-icon icon-plus-minus plus" data-icon="+"></div>'
			+ '</div></div>';
		},

		//electTime：选择早上中午晚上。0：早上，1：中午，2：晚上
		cut : function( electTime ){

			$("#container").html( manageFood.moreHtml );
			
			//切换时段解开，停止加载的锁
			manageFood.stopLoad = false;

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
								// $(".more").html("没有更多菜品了");
								$(".more").html( "没有更多数据了" );

								//停止加载
								manageFood.stopLoad = true;
							}
						}
						else if( data.status == 2 ){
							//这个判断只是为了安全以免出错
							
							$(".more").html( data.message );

							//停止加载
							manageFood.stopLoad = true;
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
			
			if( manageFood.stopLoad == false ){
				if( manageFood.loadFlag ){

					//正在加载还未成功时候不允许加载，防止重复
					manageFood.loadFlag = false;
					$.ajax({
						type : "get",
						url : "http://bd.koudaigongshe.com/mobile/index/apishow/when/"
						+ obj.when +"/canteen/"
						+ obj.canteen +"/canteen_port/"
						+ obj.cantentPort +"/len/"
						+ obj.len,
						dataType : "json",
						success : function(data){
							obj.success(data);
							manageFood.loadFlag = true;

							// console.log( obj.len );
							// console.log( data );
							// console.log( initFood );
						} 
					});
				}
			}

		},

		//下单时间渲染
		placeOrderTime : function(data){
			var time = data.data.time,
				building = data.building;
			// console.log(data);

			manageFood.orderTime[0] = "暂未开通";
			manageFood.orderTime[1] = 
			  countTimestamp( time.lunch_send_start, 8 ) 
			+ "~" 
			+ countTimestamp( time.lunch_send_end, 8 ) 
			+ "配送";
			manageFood.orderTime[2] = 
				  countTimestamp( time.dinner_send_start, 8 ) 
				+ "~" 
				+ countTimestamp( time.dinner_send_end, 8 )
				+ "配送";
			for( var i = 3; i --; ){
				$("#tab a").eq(i).children("p").html( manageFood.orderTime[ i ], 0 );
			}

			
			
			
			(function(){
				var temp = parseInt( ( new Date().getTime() 
					 - ( new Date( new Date().toLocaleDateString().replace( /月|年/g, "/" ).replace( "日", "" ) 
					 			+ ' 00:00:00' ).getTime()  ) ) / 1000 );
				//获取00：00到当前时间的秒数
				// console.log( temp );
				// var temp = 19 * 3600 + 0*60;
				// console.log( countTimestamp( temp, 0 ) );
				// console.log( temp );
				
				var result = ( Math.ceil( temp/600 ) * 600 ) + 30*60;

				// alert( countTimestamp( result, 0 ) );

				for( var i in time ){
					time[i] = parseInt( time[i] );
				}
				// console.log( time );
				// console.log( countTimestamp( time.dinner_order_end, 0 ) );
				// console.log( countTimestamp( time.dinner_pack_start, 0 ) );
				// console.log( countTimestamp( time.dinner_send_end +8*3600, 0 ) );
				// console.log( countTimestamp( time.dinner_send_start +8*3600, 0 ) );
				// alert( new Date( new Date().toLocaleDateString().replace( /月|年/g, "/" ).replace( "日", "" ) + ' 00:00:00' ).getTime()  ); 
	

				
				manageFood.hint[1] = "现在支付预计<span>" 
								+ countTimestamp( time.lunch_send_start + 30*60 , 8 ) 
								+ "</span>送达";
				manageFood.hint[2] = "现在支付预计<span>" 
								+ countTimestamp(  time.dinner_send_start + 30*60 , 8 )
								+ "</span>送达";

				building.confirmAdd = 2;//用来传到confirm页面 1为寝室 2 为楼下

				if( temp > ( time.lunch_pack_start )
					&& temp <= time.lunch_order_end ){
					if( temp > ( time.lunch_send_start + 8*3600 ) ){
						manageFood.hint[1] = "现在支付预计<span>" 
									+ countTimestamp( result , 0 ) + "</span>送达";
					} else if( time.lunch_pack_start > 0 ){
						manageFood.hint[1] = "现在支付预计<span>" 
									+ countTimestamp( time.lunch_send_start + 40*60 , 8 ) 
									+ "</span>送达";
					} 
					if( building.nd_tohome == 1 ){
						manageFood.hint[1] += "寝室";
						if( manageFood.nowType == 1 ){
							building.confirmAdd = 1;
						}
					} else {
						manageFood.hint[1] += "楼下";
					}
				} else {
					if( building.ist_tohome == 1 ){
						manageFood.hint[1] += "寝室";
						if( manageFood.nowType == 1 ){
							building.confirmAdd = 1;
						}
					} else {
						manageFood.hint[1] += "楼下";
					}

					if( temp > time.lunch_order_end ){
						manageFood.hint[1] = "现在支付预计<span>明天" 
								+ countTimestamp( time.lunch_send_start + 30*60 , 8 ) 
								+ "</span>送达";
					}
				}


				if( temp > time.dinner_pack_start
					&& temp <= time.dinner_order_end ){
					if( temp > ( time.dinner_send_start + 8*3600 ) ){
						manageFood.hint[2] = "现在支付预计<span>" 
									+ countTimestamp( result, 0 ) + "</span>送达";
					} else if( time.dinner_pack_start > 0 ){
						manageFood.hint[2] = "现在支付预计<span>" 
									+ countTimestamp( time.dinner_send_start + 40*60, 8 ) 
									+ "</span>送达";
					}
					if( building.nd_tohome == 1 ){
						manageFood.hint[2] += "寝室";
						if( manageFood.nowType == 2 ){
							building.confirmAdd = 1;
						}
					} else {
						manageFood.hint[2] += "楼下";
					}
				} else {
					if( building.ist_tohome == 1 ){
						manageFood.hint[2] += "寝室";
						if( manageFood.nowType == 2 ){
							building.confirmAdd = 1;
						}
					} else {
						manageFood.hint[2] += "楼下";
					}

					if( temp > time.dinner_order_end ){
						manageFood.hint[2] = "现在支付预计<span>明天" 
								+ countTimestamp( time.dinner_send_start + 30*60 , 8 ) 
								+ "</span>送达";
					}
				}
				manageFood.upTo( manageFood.nowType );
			}());

			//把时间戳计算成时间，每天 8 点为基准,返回时间格式 12:11
			function countTimestamp( time, start ){
				// 每天 8 点为基准 所以加 8
				var hour = parseInt( time / 3600 ) + start,
					minute = parseInt( time % 3600 / 60 );
				var sHour = hour > 9 ? hour : '0' + hour,
					sMinute = minute > 9 ? minute : '0' + minute;

				return sHour + ':' + sMinute;
			}
		}

	};
	//数据自动更新
	dynamicLoad(function(){
		manageFoodeAjaxFood();
	});	
	function manageFoodeAjaxFood(){
		//用于递归调用，用于无缝加载
		manageFood.ajax({
			when : manageFood.nowType + 1,
			canteen : manageFood.nowCanteen,
			cantentPort : manageFood.nowPort,
			len : initFood[ manageFood.nowType ].length,
			success : function(data){
				// console.log(data);
				if( data.status == 1 ){
					manageFood.upload( data.data.dish );
					
					// console.log( data.message );

					if( data.data.dish.length < 10 ){
						$(".more").html( "没有更多数据了" );

						//停止加载
						manageFood.stopLoad = true;
					}
				}
				else if( data.status == 2 ){
					$(".more").html( data.message );

					//停止加载
					manageFood.stopLoad = true;
				}
			}
		});
	}

	/*********END骚辉**********/

	$("#header").tap(function () {		//餐厅触发滑动
		slide($canteen).toggle();
	});

	/********骚辉更改***********/
	(function(){

		//菜单初始化
		var memu = {
			onOff : false,
			time : null,
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
			if(!memu.time){
				memu.time = setTimeout(function(){
					memu.time = null;
					memu.move();
				}, 300);
			}
			if( $( "#canteen" ).css('top') != '0px' ){
				e.stopPropagation();
			}
		});
		$(".memu-pull-down").tap(function(e){
			e.stopPropagation();
		});
		$(document).tap(function(){
			if( memu.onOff == true ){
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

		//重新计算要提示的时间，并传给确认支付页面
		manageFood.placeOrderTime( manageFood.data );

		$("#submit-data").prop("action", subUrl() );

		//提示用户内容
		// alert("现在系统正在升级，预计一天之内恢复！");

		return true;

		
		function subUrl(){
			var time = manageFood.data.data.time,
				url = $("#submit-data").prop("action") + '/';
			url += '((data:\'' + $("#up_to span").html() + '\','
				+ 'address:' + manageFood.data.building.confirmAdd
				+ '))' ;
			return url;
		}
	});
	$(document).on("tap", ".icon-plus-minus", function (ev) {
		var $that = $(this)
		,	$food_est = $that.closest(".food");
		var	type = $that.attr("data-icon");				//弹出式菜单————获取icon类型 用来判断+-号
		var $food = findFood($food_est);				//获取符合条件的菜式卡片和菜单条目

		if ($food.length > 1) {
			updateFood.call(this, type, $food, this);
		} else {
			if (type === "+"){
				if( !salesToggle.call( $food.find(".food-amount") , 'salesShow' , '+' ) )
					return;
				createItemDD($food_est);
			}
		}


		updateOrderIcon();
		var height = window.innerHeight - 130;
		if( $order_list.height() < height ){
			slide($order_list).update();
		}
		else {
			$order_list.css({
				'height' : height + 'px',
				'overflow-y' : 'scroll',
				'-webkit-overflow-scrolling' : 'touch',
				'background' : '#FFF'
			});
		}
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

		//销量初始化
		manageFood.cut( manageFood.nowType );

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
			if( !salesToggle.call( $food_amount , 'salesShow' , '+' ) ){
				return;
			}
			amount++;
			food_all_amount++;
		} else if (type === "-") {
			if (amount === 1) {
				food.filter(".order-list-item").remove();
				var $card_food = food.filter(".card");
				$card_food.find(".minus").addClass("hidden");
				$card_food.find(".food-amount").addClass("hidden");
				slide($order_list).update();
				salesToggle.call( $food_amount , 'salesHide' , '-' );
			} else {
				salesToggle.call( $food_amount , 'salesShow' , '-' );
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
		$item_food_total.html("￥" + price.toFixed(2) );	//弹出式菜单-当前选取菜式总价-更新
	}

	//添加菜品到购物车的时候，显示当前销量
	//如果销量已经吴则返回 false 否则 true
	function salesToggle( key , type ){
		//key : salesHide,salesShow
		var parent = $(this).parent(".food-icon-group").prev().prev().children();
		var seles = parseInt( parent.children(".food_sales_show").html() );
		var limit = parent.children(".food_sales_show").attr('data-limit');
		var amount = parseInt( $(this).text() );


		if( type == '+' ){
			if( limit == '1' && amount >= 1 ){
				alert( "每人限购一份" );
				return false;
			}
			if( seles == 0 ){
				alert( "此菜品已经被抢购完！请下次继续抢购" );
				return false;
			}
			seles--;
		} else if ( type == '-' ) {
			seles++;
		}
		parent.children(".food_sales_show").html( seles + '份' );

		if ( key == 'salesHide' ) {
			parent.children(".food_sales_hide").show();
			parent.children(".food_sales_show").hide();

		} else if( key == 'salesShow' ){
			parent.children(".food_sales_hide").hide();
			parent.children(".food_sales_show").show();
		}

		return true;
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
		$order_price.html( "￥" + total.toFixed(2) );
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