;
$(function(){

	var RANKING_URL = openUrl;
	var DATE_LIST_URL = dateValUrl;

	var listType = 'type_pocket',
		dateType = {
			type : 'day_list',
			date : '0'
		};
	var dateListVal = {};
	var ApppendList = {

		append : function( msg ){
			var _this = this;
			var result = '';


			msg.forEach(function( value, index ){
				result += _this.addEle( {
					type : index,
					data : value
				} );
			});
			$("#content_body").html( result );
		}
		,

		addEle : function(msg){
			//msg {
			//	type : 0 'is_me' / 1 'first' / 2 'second' / 3 'thirdly' / >= 4 'common',
			//	data : {
			//		id : 1,//排名
			//		name : "口袋高校助手",
			//		weChatId : pocket,
			//		img : "pocket",
			//		score : 255//指数
			//	}
			//}
			
			var type = msg.type,
				data = msg.data,

				top = [
					'first',
					'second',
					'thirdly'
				],

				isMeClass = '',
				topClass = '',
				topScoreClass = '',
				excellentClass = 'class="excellent"',

				id = data.id,
				name = data.name,
				weChatId = data.weChatId,
				score = data.score,
				img = '<div class="img"><img src="http://open.weixin.qq.com/qr/code/?username='+ data.weChatId +'"></div>'
				;

			if( type == 0 ){
				isMeClass = 'class="is-me-data"';
		    }else if( type > 0 && type < 4 ){
		    	topClass = 'num-' + top[ type - 1 ];
		    	topScoreClass = 'class="exp-'+ top[ type - 1 ] +'"';
		    }
		    else {
		    	// img = '';
		    	// excellentClass = 'class="common"';
		    }

		    return '<tr '+ isMeClass +'>' 
		        +  '<td><span class="num '+ topClass +'">'+ id +'</span></td>' 
		        +  '<td '+ excellentClass +'>'
		        +  	img
		        +  	'<span>'+ name +'</span>'
		        +  	'<small>'+ weChatId +'</small>'
		        +  '</td> '
		        + ' <td><span '+ topScoreClass +'>'+ score +'</span></td>'
		        +'</tr>';
		}
	};

	var CtrList = {

		selectChange : function (msg){
		
			CtrList.ajax({
				data : {
					listType : msg.listType,
					dateType : msg.dateType
				},
				success : function(msg){
					ApppendList.append(msg);
				}
			});
		},
		ajax : function( msg ){
			var data = msg.data;
			if( listType != data.listType || dateType != data.dateType ){
				//如果listType、dateType 与原始值相等则不更新
				
				//如果listType、dateType 为 undefined 则不更新原始值
				if( data.listType ){
					listType = data.listType;
				}
				if( data.dateType ){
					dateType = data.dateType;
				}
				
				
				var type = dateType.type;
				var date = parseInt( dateType.date ) - 1;
				$(".date_val").html( dateListVal[type][date] );

				$("#loading").show();
				$.ajax({
					url : RANKING_URL,
					type: "GET",

					// data : {
					// 	listType : 'type_all' (所有) / 'type_pocket' (口袋高校榜),
					// 	dateType : {
					// 		type : 'day_list' (日榜)/ 'week_list' (周榜) 'month_list' (月榜),
					// 		date : 后台所要的标识时间戳
					// 	}
					// }
					data : {
						listType : listType,
						dateType : dateType
					},
					dataType : "json",
					success : function(message){
						$("#loading").hide(300);
					/*	console.log(message);
						return;*/
						msg.success(message);
					}
					,
					error : function(msg){
						console.log(msg);
					}

				});
			}
		}
	};


	var DateList = {

		init: function(){

			$.ajax({
					url : DATE_LIST_URL,
					type: "GET",
					data : {},
					dataType : "json",
					success : function(msg){
						// console.log(2)
						var result = DateList.appendDateList( msg );
						
						dateListVal = msg;
						CtrList.selectChange({});
						
						$("#date_type").html( result );
						idEvent();
					}
					,
					error : function(msg){
						console.log(msg);
					}

				});
		},
		addDateEle : function ( key, val ){
			key++;
			return '<option value="'+ key +'">'+ val +'</option>';
		},
		appendDateList : function (msg){

			var dayList = '',
				weekList = '',
				monthList = '';
			msg['month_list'].forEach(function( val, key ){
				monthList += DateList.addDateEle( key, val );
			});
			msg['week_list'].forEach(function( val, key ){
				weekList += DateList.addDateEle( key, val );
			});
			msg['day_list'].forEach(function( val, key ){
				dayList += DateList.addDateEle( key, val );
			});

			var result = '<label>'
			+	'<p>月榜</p>'
			+	'<select id="month_list">'
			+		monthList
			+	'</select>'
			+	'<span></span>'
			+'</label>'
			+'<label>'
			+	'<p>周榜</p>'
			+	'<select id="week_list">'
			+		weekList
			+	'</select>'
			+	'<span></span>'
			+'</label>'
			+'<label class="nav-date-choiced">'
			+	'<p>日榜</p>'
			+	'<select id="day_list">'
			+		dayList
			+	'</select>'
			+	'<span></span>'
			+'</label>';

			return result;
		}
	}
	
	
	

/******************************/
	
	//初始化 init
	DateList.init();

	
	function idEvent(){
		$("#date_type select").click( function(){
			$("#date_type label").removeClass("nav-date-choiced");
			$(this).parent().addClass("nav-date-choiced");
	
			CtrList.selectChange({
				listType : listType,
				dateType : {
					type : $(this).prop('id'),
					date : $(this).val()
				}
			});
		} );
		$("#date_type select").change( function(){
			CtrList.selectChange({
				listType : listType,
				dateType : {
					type : $(this).prop('id'),
					date : $(this).val()
				}
			});
		} );
	
		$("#list_type div").click(function(){
	
			$("#list_type div").removeClass("nav-type-choiced");
			$(this).addClass("nav-type-choiced");
	
			
			CtrList.selectChange({
				listType : $(this).prop("id"),
				dateType : dateType
			});
		});
	}


/******************************/

});