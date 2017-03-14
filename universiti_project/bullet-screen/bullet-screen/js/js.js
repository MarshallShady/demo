
var WINDOW_WIDTH = window.innerWidth;
var WINDOW_HEIGHT = window.innerHeight;

window.onload = function()
{
	//logoc初始化
	Init();

	$("#loading").fadeOut( 500 );


	//获取canvas
	var canvas = document.getElementById("content");

	canvas.width = WINDOW_WIDTH;
	canvas.height = WINDOW_HEIGHT;

	var ctx = canvas.getContext('2d');

	var oShow = {

		//字体的属性
		textProperty : {

			LineHeight : 70,

			speed : 10,

			color : [
				"#ffffff","#00CCFF","#29e724","#8aa1d6","#66FFFF","#efe835","#57f1d6","#66ffcc","#ffff00","#00ff30","#66ff00"
			],

		},

		//要显示的数据，获取的数据存放在此数值 按照一定的格式：
		aData : [
			// {
			// 	text : "这里是显示文字的内容1" , 
			//  x :  x轴的位置  , 
			//	y : 第几通道 , 
			//	width : 这个文本有多宽 , 
			//	textSize : 字体大小 ,
			//	color : 字体颜色
			// },
		],

		//初始化背景 和头部标题显示
		init : function (){

			ctx.beginPath();
			var color = ctx.createLinearGradient(0, 0, 0, WINDOW_HEIGHT);
                color.addColorStop(0, 'rgba( 0,0,0,1.0 )');
                color.addColorStop(1, 'rgba( 0,0,36,0.5 )');
            ctx.fillStyle = color;
			ctx.fillRect( 0 , 0 , WINDOW_WIDTH , WINDOW_HEIGHT );

			ctx.beginPath();
			ctx.font = "bold 40px Arial";
			ctx.fillStyle = "#fff";
			ctx.textAlign = "center";

			ctx.fillText( "王赏作品" , WINDOW_WIDTH/2 , 80 );
		},

		//数据显示函数
		dataShow : function(){

			ctx.clearRect( 0,0,WINDOW_WIDTH,WINDOW_HEIGHT );
			this.init();
			for( var i = 0 ; i < this.aData.length ; i++ )
			{
				ctx.beginPath();
				ctx.font = this.aData[i].textSize + "px Microsoft YaHei";
				ctx.fillStyle = this.aData[i].color;
				ctx.textAlign = "left";

				ctx.fillText( this.aData[i].text , this.aData[i].x , this.aData[i].y * this.textProperty.LineHeight + 80 );
				
			}
		},

		//获取到数据 增加到 aData数组里面
		addDataInitPlace : function( data ){
			var that = this;

			data = CheckArray( data );

			//如果多于300条则延时生成 防止数组元素太多占用内存太大卡机
			if( this.aData.length > 100 )
			{
				muchDelay();
			}
			else
			{
				addData();
			}

			function muchDelay()
			{
				setTimeout(function(){
					if( this.aData.length > 100 )
					{
						muchDelay();
					}
					else
					{
						addData();
					}
				}, 3000);
			}

			function addData()
			{
				var dataY =  Math.ceil( Math.random() * Math.floor( (WINDOW_HEIGHT - 80) / that.textProperty.LineHeight - 1 ) ),
					dataX =  0,
					textWidth = 0,
					textSize = Math.random() * 20 + 30,
					color = that.textProperty.color[ Math.floor( Math.random() * that.textProperty.color.length ) ];
				for( var i = 0 ; i < that.aData.length ; i++ )
				{
					if( that.aData[i].y == dataY && that.aData[i].x + that.aData[i].width > WINDOW_WIDTH )
					{
						if( dataX < that.aData[i].x + that.aData[i].width )
						{
							dataX = that.aData[i].x + that.aData[i].width;
						}
					}
				}
				if( dataX == 0 )
				{
					dataX = WINDOW_WIDTH;
				}

				ctx.beginPath();
				ctx.font = textSize + "px Arial";
				textWidth = ctx.measureText( data ).width;

				that.aData.push( { 
					text : data , 
					x : dataX + 100 , 
					y : dataY , 
					width : textWidth , 
					textSize : textSize ,
					color : color
				} );
			}
		},

		//计算下一时刻的每一个字符串 运动的x轴位置
		countPlace : function(){

			for( var i = 0 ; i < this.aData.length ; i++ )
			{
				this.aData[i].x -= 2;
			}
		},

		//开定时器，动起来
		execute : function(){

			var that = this;
			this.timer = setInterval(function(){

				that.init();
				that.countPlace();
				that.dataShow();
				that.removeData();
			},that.textProperty.speed);
		},

		//当字符串，超出了显示区域 删除
		removeData : function (){
			for( var i = 0 ; i < this.aData.length ; i++ )
			{
				if( this.aData[i].x + this.aData[i].width < -100 )
				{
					this.aData.splice( i , 1 );
				}
			}
		},
	};

	//测试 模拟socket连接
	setInterval(function(){

		oShow.addDataInitPlace( "我是王小水_hduhelp_王赏弹幕墙 你值得拥有" );
	},1000);


	//建立socket连接
	// var socket = io("http://120.26.64.127:4027");


	// socket.on('chat message', function(msg){

	// 	oShow.addDataInitPlace( msg );
	// });

	oShow.execute();

	//屏幕自适应
	window.onresize=function()
	{		
		WINDOW_WIDTH = window.innerWidth;
		WINDOW_HEIGHT = window.innerHeight;

		canvas.width = WINDOW_WIDTH;
		canvas.height = WINDOW_HEIGHT;

		Init();

	};
	function Init()
	{
		// logo初始化
		$("#page").width(WINDOW_WIDTH);
		$("#page").height(WINDOW_HEIGHT);


		$(".extend").css({
			left : ( WINDOW_WIDTH - $(".extend").width() )/2 + "px",
		});

		$("#award").css({
			'height' : ( WINDOW_HEIGHT - 160 ) + 'px',
		});

		$("#vote").css({
			'height' : ( WINDOW_HEIGHT - 160 ) + 'px',
		});


	};



	/* 附加功能显示 */

	//二维码
	$("#navbar li:eq(0)").click(function(event) {

		$("#award").fadeOut( 500 );
		$("#navbar li:eq(1)").attr( 'index' , 'on' );
		
		$("#vote").fadeOut( 500 );
		$("#navbar li:eq(2)").attr( 'index' , 'on' );

		$("#organization-logo").fadeOut( 500 );

		$("#hduhelptd").fadeToggle( 500 );
	});

	//抽奖
	$("#navbar li:eq(1)").click(function(event) {

		var nameList = null , winners = 0 ;

		$("#hduhelptd").fadeOut( 500 );
		$("#vote").fadeOut( 500 );

		$("#award").fadeToggle( 500 );

		$("#organization-logo").fadeOut( 500 );

		if( $(this).attr( 'index' ) == 'off' )//控制开关如果已经打开了 只下面代码不需要执行
		{
			$(this).attr( 'index' , 'on' );
			return false;
		}
		else
		{
			$(this).attr( 'index' , 'off' );
		}
		
		window.loading();
		$.ajax( {

			type: "POST",

			url : "http://we7.hduhelp.com/mobile.php?act=module&name=danmu&do=getInfo&weid=1#mp.weixin.qq.com#wechat_redirect", 

			dataType : "json",

			success : function(msg){
				
				nameList = msg;

				//接受数据进行判断，如果学号不标准，则剔除这个数据
				for( var i in nameList.stuno )
				{
					if( !( /^1[0-9]{7}$/.test( nameList.stuno[i] ) ) )
					{
						nameList.stuno.splice(i,1);
						nameList.name.splice(i,1);
					}
				}

			    $("#loading").fadeOut( 500 );
			}
		});

		//抽奖准备
		$("#list-name").html("<div><p>开始</p><h1>你是今晚的幸运儿吗？</h1></div>");
		$("#list-name").css({

			'padding-top' : ( ( WINDOW_HEIGHT - 160 )*0.8 - $("#list-name div").height() )/2 + 'px',
		});
		//有两个按钮 一个开始 一个停
		$("#award .gbutton button:eq(0)").show();//显示开始
		$("#award .gbutton button:eq(1)").hide();//关闭停 (下面同)

		$("#award .gbutton button").unbind( "click" );

		$("#award .gbutton button:eq(0)").click(function(event) {

			//如果只有一个人则不需要抽了 
			if( nameList.stuno.length < 1 )
			{
				alert( "只剩一个名单咯，他是本场最幸运观众" );
				
				return false;
			}

			//显示第一个人
			$("#list-name").html("<div><p>" + nameList.stuno[0] + "</p></div>");
			$("#list-name").css({
				//计算竖直居中
				'padding-top' : ( ( WINDOW_HEIGHT - 160 )*0.8 - $("#list-name div").height() )/2 + 'px',
			});

			$("#award .gbutton button:eq(0)").hide();
			$("#award .gbutton button:eq(1)").show();

			clearInterval($("#award").attr('timeClear'));
			$("#award").attr('timeClear', setInterval(function(){

				if( ++winners >= nameList.stuno.length )
				{
					winners = 0;
				}
				$("#list-name p").html( nameList.stuno[winners] );
				
			},50) );

			
			
		});
		$("#award .gbutton button:eq(1)").click(function(event) {
			
			$("#award .gbutton button:eq(0)").show();
			$("#award .gbutton button:eq(1)").hide();

			if( $("#award").attr('timeClear') )
			{
				clearInterval($("#award").attr('timeClear'));
			}

			if( winners >= nameList.stuno.length )//以防止溢出
			{
				winners = 0;
			}

			//提交中奖名单，记录
			$.post("http://we7.hduhelp.com/mobile.php?act=module&name=danmu&do=insertInfo&weid=1#mp.weixin.qq.com#wechat_redirect",
				 { stuno : nameList.stuno[winners] , user : nameList.name[winners] } );

			$("#list-name").html("<div><p>" + nameList.stuno[winners] + "</p><p>"+ nameList.name[winners] +"</p></div>");

			if( nameList.stuno.length > 0 )
			{
				//删除已中奖的人，以防止在抽
				nameList.stuno.splice(winners,1);
				nameList.name.splice(winners,1);
			}
			$("#list-name").css({

				'padding-top' : ( ( WINDOW_HEIGHT - 160 )*0.8 - $("#list-name div").height() )/2 + 'px',
			});
		});
	});


	//投票显示


	$("#navbar li:eq(2)").click(function(event) {


		$("#award").fadeOut( 500 );
		$("#navbar li:eq(1)").attr( 'index' , 'on' );

		$("#hduhelptd").fadeOut( 500 );

		$("#organization-logo").fadeOut( 500 );

		$("#vote").fadeToggle( 500 );


		if( $(this).attr( 'index' ) == 'off' )//控制开关如果已经打开了 只下面代码不需要执行
		{
			$(this).attr( 'index' , 'on' );
			return false;
		}
		else
		{
			$(this).attr( 'index' , 'off' );
		}
		window.loading();

		//清空之前的数据
		$("#vote-result .paging").html('');

		//接受后台数据
		// data = [
		// 	{
		// 		namer : '这是第一个节目',
		// 		percent : 80
		// 	},
		// 	{
		// 		namer : '这是第一个节目',
		// 		percent : 60
		// 	},
		// 	{
		// 		namer : '这是第一个节目',
		// 		percent : 40
		// 	},
		// 	{
		// 		namer : '这是第一个节目',
		// 		percent : 10
		// 	},

		// ];
		$.post("http://we7.hduhelp.com/mobile.php?act=module&name=commvote&do=votes&weid=1#mp.weixin.qq.com#wechat_redirect",
		function(msg){
			var totalVotes = 0,
					temp = {
						name : '',
						votes : 0
					};
			for( var i = 0; i < msg.votes.length ; i++ )
			{
				msg.votes[i] = parseInt( msg.votes[i] );
				totalVotes += ( msg.votes[i] );
			}
			
			for( var i = 0; i < msg.votes.length ; i++ )
			{
				for( var j = i; j < msg.votes.length ; j++ )
				{
					if( msg.votes[i] < msg.votes[j] )
					{
						temp.name = msg.name[j];
						temp.votes = msg.votes[j];

						msg.name[j] = msg.name[i];
						msg.votes[j] = msg.votes[i];

						msg.name[i] = temp.name;
						msg.votes[i] = temp.votes;
					}
				}	
			}
			
			for( var i = 0; i < msg.name.length ; i++ )
			{	
				if( $("#vote-result .paging").eq(0).height() < WINDOW_HEIGHT - 300 )
				{
					$("#vote-result .paging").eq(0).append( addTemplate( msg.name[i] , 
						msg.votes[i] * 100 / totalVotes , i + 1 ) );
				}
				else 
				{
					$("#vote-result .paging").eq(1).append( addTemplate( msg.name[i] , 
						msg.votes[i] * 100 / totalVotes , i + 1 ) );
				}
			}
			$("#loading").fadeOut( 500 );
		}, "json");

		//添加进度条到上下文
		//name 名字 ，percent 百分比（只需要数字就好 比如50%  给50），where 拍第几 , color 颜色
		function addTemplate( name , percent , where )
		{
			var oColor = {
				'red' 	: '-danger' ,
				'green' : '-success',
				'blue'  : '-info' ,
				'yellow': '-warning',
			};
			var color = '';
			switch( where )
			{
				case 1: color = oColor['red']; break;
				case 2: color = oColor['green']; break;
				case 3: color = oColor['blue']; break;
				default : color = oColor['yellow']; break;
			}
			var temp = '<div class="vote-element">\
						<p>'+ where +'、'+ name +'</p>\
						<div class="progress">\
						  <div class="progress-bar progress-bar'+ color +'" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: '+ percent +'%;"> \
						  </div>\
						</div>\
					</div>';
			return temp;
		}
	});


	$("#navbar li:eq(3)").click(function(){
		$("#award").fadeOut( 500 );
		$("#navbar li:eq(1)").attr( 'index' , 'on' );

		$("#hduhelptd").fadeOut( 500 );
		$("#vote").fadeOut( 500 );
		$("#navbar li:eq(2)").attr( 'index' , 'on' );

		$("#organization-logo").fadeToggle( 500 );

	});
	
};





//敏感词过滤
function CheckArray(strVal) 
{
    var strArray = /鸡鸡|打不死你|狗生|骚货|s\s*b|妈蛋|打死|操|干|日|叼|操你妈|操你娘|你妈逼|草|叼你|叼你老母|日你|日你妈|靠|狗日的|去你妈的|去你大爷|你妹|操你妹|干你妹|日你妹|去死|滚你妈|垃圾|你去死|扑街|全家死|去死吧你|你妹的|王八|爱女人|爱液|按摩棒|拔出来|爆草|包二奶|暴干|暴奸|暴乳|爆乳|暴淫|屄|被操|被插|被干|逼奸|仓井空|插暴|操逼|操黑|操烂|肏你|肏死|操死|操我|厕奴|插比|插b|插逼|插进|插你|插我|插阴|潮吹|潮喷|成人电影|成人论坛|成人色情|成人网站|成人文学|成人小说|艳情小说|成人游戏|吃精|赤裸|抽插|扌由插|抽一插|春药|大波|大力抽送|大乳|荡妇|荡女|盗撮|多人轮|发浪|放尿|肥逼|粉穴|封面女郎|风月大陆|干死你|干穴|肛交|肛门|龟头|裹本|国产av|好嫩|豪乳|黑逼|后庭|后穴|虎骑|花花公子|换妻俱乐部|黄片|几吧|鸡吧|鸡巴|鸡奸|寂寞男|寂寞女|妓女|激情|集体淫|奸情|叫床|脚交|金鳞岂是池中物|金麟岂是池中物|精液|就去日|巨屌|菊花洞|菊门|巨奶|巨乳|菊穴|开苞|口爆|口活|口交|口射|口淫|裤袜|狂操|狂插|浪逼|浪妇|浪叫|浪女|狼友|聊性|流淫|铃木麻|凌辱|漏乳|露b|乱交|乱伦|轮暴|轮操|轮奸|裸陪|买春|美逼|美少妇|美乳|美腿|美穴|美幼|秘唇|迷奸|密穴|蜜穴|蜜液|摸奶|摸胸|母奸|奈美|奶子|男奴|内射|嫩逼|嫩女|嫩穴|捏弄|女优|炮友|砲友|喷精|屁眼|品香堂|前凸后翘|强jian|强暴|强奸处女|情趣用品|情色|拳交|全裸|群交|惹火身材|人妻|人兽|日逼|日烂|肉棒|肉逼|肉唇|肉洞|肉缝|肉棍|肉茎|肉具|揉乳|肉穴|肉欲|乳爆|乳房|乳沟|乳交|乳头|三级片|骚逼|骚比|骚女|骚水|骚穴|色逼|色界|色猫|色盟|色情网站|色区|色色|色诱|色欲|色b|少年阿宾|少修正|射爽|射颜|食精|释欲|兽奸|兽交|手淫|兽欲|熟妇|熟母|熟女|爽片|爽死我了|双臀|死逼|丝袜|丝诱|松岛枫|酥痒|汤加丽|套弄|体奸|体位|舔脚|舔阴|调教|偷欢|偷拍|推油|脱内裤|文做|我就色|无码|舞女|无修正|吸精|夏川纯|相奸|小逼|校鸡|小穴|小xue|写真|性感妖娆|性感诱惑|性虎|性饥渴|性技巧|性交|性奴|性虐|性息|性欲|胸推|穴口|学生妹|穴图|亚情|颜射|阳具|杨思敏|要射了|夜勤病栋|一本道|一夜欢|一夜情|一ye情|阴部|淫虫|阴唇|淫荡|阴道|淫电影|阴阜|淫妇|淫河|阴核|阴户|淫贱|淫叫|淫教师|阴茎|阴精|淫浪|淫媚|淫糜|淫魔|淫母|淫女|淫虐|淫妻|淫情|淫色|淫声浪语|淫兽学园|淫书|淫术炼金士|淫水|淫娃|淫威|淫亵|淫样|淫液|淫照|阴b|应召|幼交|幼男|幼女|欲火|欲女|玉女心经|玉蒲团|玉乳|欲仙欲死|玉穴|援交|原味内衣|援助交际|张筱雨|招鸡|招妓|中年美妇|抓胸|自拍|自慰|作爱|18禁|99bb|a4u|a4y|adult|amateur|anal|a片|fuck|gay片|g点|g片|hardcore|h动画|h动漫|incest|porn|secom|sexinsex|sm女王|xiao77|xing伴侣|tokyohot|yin荡|胡的接班人|钦定接班人|习近平|平近习|xjp|习太子|习明泽|老习|温家宝|温加宝|温x|温jia宝|温宝宝|温加饱|温加保|张培莉|温云松|温如春|温jb|胡温|胡x|胡jt|胡boss|胡总|胡王八|hujintao|胡jintao|胡j涛|胡惊涛|胡景涛|胡紧掏|湖紧掏|胡紧套|锦涛|hjt|胡派|胡主席|刘永清|胡海峰|胡海清|江泽民|民泽江|江胡|江哥|江主席|江书记|江浙闽|江沢民|江浙民|择民|则民|茳泽民|zemin|ze民|老江|老j|江core|江x|江派|江zm|jzm|江戏子|江蛤蟆|江某某|江贼|江猪|江氏集团|江绵恒|江绵康|王冶坪|江泽慧|邓小平|平小邓|xiao平|邓xp|邓晓平|邓朴方|邓榕|邓质方|毛泽东|猫泽东|猫则东|chairmanmao|猫贼洞|毛zd|毛zx|z东|ze东|泽d|zedong|毛太祖|毛相|主席画像|改革历程|朱镕基|朱容基|朱镕鸡|朱容鸡|朱云来|李鹏|李peng|里鹏|李月月鸟|李小鹏|李小琳|华主席|华国|国锋|国峰|锋同志|白春礼|薄熙来|薄一波|蔡赴朝|蔡武|曹刚川|常万全|陈炳德|陈德铭|陈建国|陈良宇|陈绍基|陈同海|陈至立|戴秉国|丁一平|董建华|杜德印|杜世成|傅锐|郭伯雄|郭金龙|贺国强|胡春华|耀邦|华建敏|黄华华|黄丽满|黄兴国|回良玉|贾庆林|贾廷安|靖志远|李长春|李春城|李建国|李克强|李岚清|李沛瑶|李荣融|李瑞环|李铁映|李先念|李学举|李源潮|栗智|梁光烈|廖锡龙|林树森|林炎志|林左鸣|令计划|柳斌杰|刘奇葆|刘少奇|刘延东|刘云山|刘志军|龙新民|路甬祥|罗箭|吕祖善|马飚|马恺|孟建柱|欧广源|强卫|沈跃跃|宋平顺|粟戎生|苏树林|孙家正|铁凝|屠光绍|王东明|汪东兴|王鸿举|王沪宁|王乐泉|王洛林|王岐山|王胜俊|王太华|王学军|王兆国|王振华|吴邦国|吴定富|吴官正|无官正|吴胜利|吴仪|奚国华|习仲勋|徐才厚|许其亮|徐绍史|杨洁篪|叶剑英|由喜贵|于幼军|俞正声|袁纯清|曾培炎|曾庆红|曾宪梓|曾荫权|张德江|张定发|张高丽|张立昌|张荣坤|张志国|赵洪祝|紫阳|周生贤|周永康|朱海仑|政治局常委|中纪委|主席像|总书记|中南海|大陆当局|中国当局|北京当局|共产党|党产共|gcd|共贪党|gongchandang|阿共|共一产一党|产党共|公产党|工产党|共c党|共x党|共铲|供产|共惨|供铲党|供铲谠|供铲裆|共残党|共残主义|共产主义的幽灵|拱铲|老共|中共|中珙|中gong|gc党|贡挡|gong党|g产|狗产蛋|共残裆|恶党|邪党|共产专制|共产王朝|裆中央|土共|土g|共狗|g匪|共匪|仇共|communistparty|政府|症腐|政腐|政付|正府|政俯|政一府|政百度府|政f|zhengfu|政zhi|挡中央|档中央|中央领导|中国zf|中央zf|国wu院|中华帝国|gong和|大陆官方|北京政权|李愚蠢|中国猪|台湾猪|进化不完全的生命体|震死他们|贱人|装b|大sb|傻逼|傻b|煞逼|煞笔|刹笔|傻比|沙比|欠干|婊子养的|我日你|我操|我草|卧艹|卧槽|爆你菊|艹你|cao你|你他妈|真他妈|别他吗|草你吗|草你丫|操你妈|擦你妈|操你娘|操他妈|日你妈|干你妈|干你娘|娘西皮|狗操|狗草|狗杂种|狗日的|操你祖宗|操你全家|操你大爷|妈逼|你麻痹|麻痹的|妈了个逼|马勒|狗娘养|贱比|贱b|下贱|死全家|全家死光|全家不得好死|全家死绝|白痴|无耻|sb|杀b|你吗b|你妈的|婊子|贱货|人渣|混蛋|媚外|和弦|兼职|限量|铃声|性伴侣|男公关|火辣|精子|射精|诱奸|强奸|做爱|性爱|发生关系|按摩|快感|处男|猛男|少妇|屌|屁股|下体|a片|内裤|浑圆|咪咪|发情|刺激|白嫩|粉嫩|兽性|风骚|呻吟|sm|阉割|高潮|裸露|不穿|一丝不挂|脱光|干你|干死|我干|中日没有不友好的|木牛流马的污染比汽车飞机大|他们嫌我挡了城市的道路|当官靠后台|警察我们是为人民服务的|中石化说亏损|做人不能太cctv了|领导干部吃王八|工商税务两条狼|公检法是流氓|公安把秩序搞乱|剖腹一刀五千几|读不起选个学校三万起|父母下岗儿下地|裙中性运动|fuck|草泥马/ig;

    var userName = strVal.match( /[^\s]*?(?=_hduhelp_)/ );
    var userData = strVal.replace( /[^\s]*?_hduhelp_/ , '' );

    return ( userName + '：' + userData.replace(strArray,'***') );
}