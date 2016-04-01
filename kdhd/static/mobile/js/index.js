// TouchSlide({ 
// 	slideCell:"#banner",
// 	interTime:2500,
// 	autoPlay:true
// });

$(document).ready(function(ready) {
	var food_all_amount = 0					//初始菜数
	,	$order_list = $("#order-list")		//菜单列表
	,	$orderInfo = $("#order-icon")		//弹出式菜单icon & 当前总选菜数
	,	$container = $("#container")		//获取卡片容器
	,	$canteen = $("#canteen");			//选择餐厅

	/*********骚辉**********/
	//菜品生成
	var manageFood = {

		nowType : 0,
		status : [
			'breakfast',
			'lunch',
			'dinner'
		],
		init : function(){
			manageFood.creats( manageFood.nowType );
			$("#tab li").tap(function(){
				var temp = clearShopping('切换时间点将会清空购物车，您确定切换吗？');
				if(!temp)return;
				manageFood.creats( $(this).attr('data-flag') );
				$("#tab li a").removeClass();
				$(this).children().addClass('current-time');
				manageFood.nowType = $(this).attr('data-flag');
			}); 
		},

		//菜品生成函数，一次生成一个页面上的 dom 
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
		creatFood : function( num, data ){
			return '<div class="card food row"  data-number="'+ num +'"><img class="card-object	food-image col-7" src="'+ data.img +'" ><div class="card-text col-5"><ul class="food-info card-middle card-right large"><li class="food-name icon-after" data-icon="HOT">'+ data.name +'</li><li class="food-components">'+ data.introduce +'</li><li class="food-sold icon-before" data-icon="售">'+ data.sellNum +'份</li><li class="food-unitprice warning">￥'+ data.cost +'</li></ul></div><div class="card-box food-box"></div><div class="food-icon-group"><div class="food-icon icon-plus-minus hidden minus" data-icon="-"></div><span class="food-amount hidden">0</span><div class="food-icon icon-plus-minus plus" data-icon="+"></div></div></div>';
		},

		//electTime：选择早上中午晚上。0：早上，1：中午，2：晚上
		creats : function( electTime ){
			var numFood = 0;
			var result = '';
			for( var i = 0, len = initFood[electTime].length ; i < len; i++ ){
				result += this.creatFood( i, initFood[electTime][i] );
			}
			$("#container").html('');
			$("#container").append( result );
		}
	};
	/*********END骚辉**********/


	$("#header").tap(function () {		//餐厅触发滑动
		slide($canteen).toggle();
	});



	/********骚辉更改***********/
	(function(){
		//防止选择餐厅的a标签冒泡
		$("#canteen a").tap(function(e){
			e.stopPropagation();
		});


		//切换档口
		var $restaurants = $("#restaurant a");
		for( var len = $restaurants.length; len--; ){
			(function(num){
				$($restaurants[num]).tap(function(){
					$("#restaurant p").html( $(this).html() );
					$("#booth .booth-ele").css( 'display', 'none' );
					$( $("#booth .booth-ele")[num] ).css( 'display', 'block' );
				});
			}(len));
		}

		//给 canteen 的 scroll 增加交互
		pageScoll( $("#canteen")[0], 5 );

		
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
	$("#order-enter").tap(function () {
		var $order_input = $("#order-input")
		,	order = [];			//弹出式菜单————获取所有已选菜式的总价
		$order_list.find(".order-list-item").each(function () {
			order.push({
				amount : parseInt($(this).find(".item-food-amount").text()),
				number : $(this).attr("data-number")
			});
		});
		var result = {
			type :  manageFood.status[ manageFood.nowType ],
			data : order
		};
		$order_input.val(JSON.stringify(result));
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
			$order_enter.removeAttr("disabled");
			$order_enter.css("backgroundColor","#ff6550");
		} else {
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