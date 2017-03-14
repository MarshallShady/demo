window.onload = function ()
{
	$1('#loading').style.display = 'none'; //有请求的地方都需要改
	var	num = 0,//显示区标志
		offalert = 1;
	var oBox = $1('#box');
	var oHead = $1('#head1'),
		oHeadInput = $1('input',oHead)[0],
		oHeadButtom = $1('button',oHead)[0],
		oHeadSearch = $1('#search-text'),
		aHeadSearchUl = $1('ul',oHeadSearch),
		aHeadSearchUlLi = $1('li',aHeadSearchUl[0]),
		oHeadSearchSub = $1('#search-sub'),
		aHeadSearchSubLi = $1('li',oHeadSearchSub);
	var aDiv = $1('div',document),aPage = [],
		aMore = $1('span',document);
	var oIntroduction = $1('#introduction'),
		oLibrary = $1('ul',$1('#library'))[0],
		oLibraryButton = $1('button',$1('#library'))[0],
		aLibraryOl = [
				'<li>校区</li><li>馆藏地</li><li>书刑状态</li>','<li>借阅时间</li><li>应还时间</li><li>续借次数</li>'
				];
	for(var i=0;i<aDiv.length;i++)//获取四个主页面div
	{
		if(aDiv[i].className == 'page')
		{
			aPage.push(aDiv[i]);
		}
	}
	for(var i=0;i<3;i++)
	{
		aPage[i].aUl = $1('ul',aPage[i]);
		for(var j=0;j<aPage[i].aUl.length;j++)
		{
			aPage[i].aUl[j].aLi = $1('li',aPage[i].aUl[j]);
		}
	}
	for(var i=0;i<aPage.length;i++)
	{
		aPage[i].style.minHeight = oHeight +'px';
	}
	//接受后数据区
	var arrData = [//书名不超过15个中文字符
		[//0  初始化推荐消息
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 率论与数理统率论与数理统率论与数理统率论与数理统',zz:'作者',cs:15,kj:'可借',num:'48.4/6202',press:'（出版社）杭电出版',
				withs:[
					{
					school:'（校区）下沙',collection:'馆藏地5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'（书型状态）啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},
		],
		[//1 初始化预约消息
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},
		],
		[//2 初始化我的当前借阅
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
						school:'2015-3-22',collection:'2015-3-55',
						state:'10',overdue : 10
					}
				]
			},
		],
		[//3 初始化借阅历史
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},
		],
		[//4 搜索历史初始化
			'search','search','search','search','search','search','search','search','search'
		],
		[//5 请求搜索内容
			{
				img : 'img/imgno.jpg',name:'概率论与数理统计 (浙大四版) 同',zz:'是傻逼',cs:15,kj:'可借',num:'帮48.4/6202',press:'杭电出版',
				withs:[
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					},
					{
					school:'11下沙',collection:'5路阿飞阿富汗肺结核啊很疯狂啊回答是否',
					state:'啊手动阀手动阀手动阀杰哈德是否'
					}
				]
			},
		],
		[//6 是否有更多的请求
			1,0,0,1,,1
		]
	];
	var arrSubData = ['','','','','',''];

	//这里有一个初始化数据请求
	
	fnWith();//事件初始化
	fnHistory();
	//接受后数据区结束

	oHeadInput.style.left = (oWidth - oHeadInput.offsetWidth)/2 +'px';
	oHeadInput.onclick = function()
	{
		oHeadInput.style.left = '4%';
		oHeadInput.style.top = '10px';
		oHeadButtom.style.display = 'block';
		oHeadSearch.style.cssText = 'left:0;top:46px;min-height:'+(oHeight-94)+'px;';
		oBox.style.height = oHeadSearch.offsetHeight + 104 +'px';
		oHeadButtom.onclick = fnHeadButtom;
		setTimeout(function(){
			document.body.scrollTop = 0;
			offalert&&(alert('点击回车搜索'),offalert--);//提示
		},300);
		oHeadInput.onkeydown = function (e)
		{
			var oEvent = e || window.event || arguments.callee.caller.arguments[0];
			if(oEvent.keyCode == 13)//请求搜索
			{	
				if(this.value != '')
				{
					//这里有一个搜索请求
					arrData[4].push(this.value);
					fnHistory();
					fnHeadSearch();
				}
			}
		};
		
	};


	//详细页返回访问页
	var oReturn = $1('#returns');
	oReturn.onclick =function()
	{
		fnReturn();
	}
	//详细页返回访问页结束
	//菜单区
	var oTail = $1('#menu');
	var aTailDiv = $1('div',oTail);

	for(var i=0;i<aTailDiv.length;i++)
	{	
		aTailDiv[i].index = i;
		aTailDiv[i].ontouchstart = function(ev)
		{
			var oEvent=ev||event;
			oEvent.stopPropagation();
			this.ontouchend = function(ev)
			{
				var oEvent=ev||event;
				fnHeadButtom();
				oEvent.stopPropagation();
				fnTail(this.index);
				num = this.index;
			};
		};
	}

	document.ontouchstart = function (ev)//滑动翻页
	{
		var oEvent=ev||event;
		var singleTouch = oEvent.changedTouches[0];
		var tMouseDown=singleTouch.pageX;//记录手触屏时的坐标

		document.ontouchend=mouseUp;
		function mouseUp(ev)
		{
			var oEvent=ev||event;
			var singleTouch = oEvent.changedTouches[0];
			var tMouseUp=singleTouch.pageX;
			if((tMouseDown - tMouseUp)>50)
			{
				if(num<2)
				{
					fnHeadButtom();
					fnTail(++num);
				}
			}
			else if((tMouseUp - tMouseDown)>50)
			{
				if(num>0)
				{
					if(aPage[3].off == 1)
					{
						fnReturn();
					}
					else
					{
						fnHeadButtom();
						fnTail(--num);
					}
				}
			}
		}
	};
	//菜单区结束


	//函数区
	function fnHeadButtom()//取消搜索框
	{
		oHeadInput.style.left = (oWidth - oHeadInput.offsetWidth)/2 +'px';
		oHeadInput.style.top = '150px';
		oHeadButtom.style.display = 'none';
		oHeadSearch.style.top = '900px';
		setTimeout(function(){
			oHeadSearch.style.left = '-200%';
		},300);
		aHeadSearchUl[0].style.display = 'block';
		oHeadSearchSub.style.display = 'none';
		oBox.style.height = aPage[num].offsetHeight + 48 +'px';	
	}
	function fnHeadSearch()//搜索内容显示
	{
		aHeadSearchUl[0].style.display = 'none';
		oHeadSearchSub.style.display = 'block';
		fnAddData(5,5);
		aHeadSearchUl[1].innerHTML = arrSubData[5];
		for( var i=0;i<aHeadSearchSubLi.length;i++ )
		{
			aHeadSearchSubLi[i].withs = [5,i];
			aHeadSearchSubLi[i].onclick = function()
			{
				$1('ol',$1('#library'))[0].innerHTML = aLibraryOl[0];
				oLibraryButton.style.display = 'none';
				oHeadSearch.off = 1;
				oHeadSearch.style.cssText = 'left:-200%;top:900px;';
				fnWithData(this);
				fnTail(3);
			};
		}
		fnMore();
	}

	function fnWith()//初始化点击事件（展示的Li）如有更新请调用此函数
	{
		fnAddData(0,arrData.length-3);
		for(var i=0;i<3;i++)
		{
			for(var j=0;j<aPage[i].aUl.length;j++)
			{
				aPage[i].aUl[j].innerHTML = arrSubData[i+j];//测试
				for(var x=0;x<aPage[i].aUl[j].aLi.length;x++)
				{
					aPage[i].aUl[j].aLi[x].withs = [i+j,x];
					if(i==2&&j==0)
						aPage[i].aUl[j].aLi[x].me = 1;
					aPage[i].aUl[j].aLi[x].onclick = function ()
					{
						if(this.me == 1)
						{
							$1('ol',$1('#library'))[0].innerHTML = aLibraryOl[1];
							oLibraryButton.style.display = 'block';
							oLibraryButton.onclick = function()//续借请求
							{
								this.innerHTML = '正在处理...';
								//这里有一个请求
								//请求完毕 ： oLibraryButton.style.background = '#fff';
								//oLibraryButton.innerHTML = data;
							};
						}
						else
						{
							$1('ol',$1('#library'))[0].innerHTML = aLibraryOl[0];
							oLibraryButton.style.display = 'none';
						}
						fnWithData(this);
					}
				}
			}
		}
		fnMore();
		oBox.style.height = aPage[num].offsetHeight + 48 +'px';	
	}
	function fnAddData(min,max)//编写要要添加的html代码到数组
	{
		for(var i=min;i<=max;i++)
		{
			if(arrData[i].length != 0)
			{
				arrSubData[i] = '';
				for(var j=0;j<arrData[i].length;j++)
					arrSubData[i]+='<li><div class="book-img"><img src="'+arrData[i][j].img+'"></div><div class="book-name"><p>'+arrData[i][j].name+'</p><span>'+arrData[i][j].zz+'<br>'+arrData[i][j].cs+'次借阅<br>'+arrData[i][j].kj+'</span></div><div style="clear:both"></div></li>';
				if(arrData[6][i] == 1)
					arrSubData[i] += '<span class="more">更多</span>';
			}
			else
			{
				arrSubData[i] = '<span class="no-data">暂无数据</span>';
			}
		}
	}



	function fnHistory()//搜索历史显示
	{
		arrSubData[4] = '';
		for(var i=0;i<arrData[4].length;i++)
		{
			arrSubData[4]+='<li>'+arrData[4][i]+'</li>';
		}
		aHeadSearchUl[0].innerHTML = arrSubData[4];
		
		for( var i=0;i<aHeadSearchUlLi.length;i++ )
		{
			aHeadSearchUlLi[i].onclick = function()
			{
				//这里有一个搜索请求
				fnHeadSearch();
				oHeadInput.value = this.innerHTML;
			};
			
		}
	}

	function fnWithData(that)//需要数据 获取详细信息面页
	{
		var aLibraryData = '';
		aPage[3].off = 1;
		oIntroduction.innerHTML = '<p>《'+ arrData[that.withs[0]][that.withs[1]].name +'》</p><div class="book-img"><img src="'+ arrData[that.withs[0]][that.withs[1]].img +'"></div><div class="book-name"><p>作者：'+ arrData[that.withs[0]][that.withs[1]].zz +'<br><br>索书号：'+ arrData[that.withs[0]][that.withs[1]].num +'<br><br>'+arrData[that.withs[0]][that.withs[1]].cs+'次借阅<br><br>出版社：'+ arrData[that.withs[0]][that.withs[1]].press +'</p></div>';
		for(var i=0;i<arrData[that.withs[0]][that.withs[1]].withs.length;i++)
		{
			aLibraryData += '<li><span>'+arrData[that.withs[0]][that.withs[1]].withs[i].school+'</span><span>'+arrData[that.withs[0]][that.withs[1]].withs[i].collection+'</span><span>'+arrData[that.withs[0]][that.withs[1]].withs[i].state+'</span></li>';
		}
		oLibrary.innerHTML = aLibraryData;
		$1('#loading').style.display = 'block';
		setTimeout(function(){
			fnTail(3);
			$1('#loading').style.display = 'none';
		},600);
	}
	function fnMore()
	{
		for(var i=0; i<aMore.length;i++)
		{
			if(aMore[i].className == 'more')
			{
				aMore[i].onclick = function()
				{
					this.innerHTML = '加载中...';
					//这里有一次请求
					// fnWith();//加载完数据后执行
					//fnHeadSearch();
				}
			}
		}
	}

	var times;
	function fnTail(iData)//iData为可视区显示第几个page
	{
		clearTimeout(times);
		aPage[iData].className = 'page move';
		aPage[iData].style.zIndex = 1;
		aPage[iData].style.left = 0;
		times = setTimeout(function(){
			for(var i=0;i<aPage.length;i++)
			{	
				aPage[i].className = 'page';
				if(i<iData)
				{
					aPage[i].style.left = '-100%';
				}
				else if(i>iData)
				{
					aPage[i].style.left = '100%';
				}
				aPage[i].style.zIndex = 0;
			}
		},300);
		oBox.style.height = aPage[iData].offsetHeight + 48 +'px';
		document.body.scrollTop = 0;
	}

	function fnReturn()//详细页返回访问页
	{
		fnTail(num);
		aPage[3].off = 0;
		if(oHeadSearch.off == 1)
		{
			oHeadSearch.off = 0;
			oHeadSearch.style.cssText = 'left:0;top:46px;min-height:'+(oHeight-94)+'px;';
		}
	}
	//函数区结束
};
function $1(id,father)//元素选择
{
	if(id[0] == '#')
		return document.getElementById(id.substring(1,id.length));
	return father.getElementsByTagName(id);
}