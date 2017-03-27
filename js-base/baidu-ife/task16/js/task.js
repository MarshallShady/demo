/**
 * aqiData，存储用户输入的空气指数数据
 * 示例格式：
 * aqiData = {
 *    "北京": 90,
 *    "上海": 40
 * };
 */
var aqiData = {
	"北京": 90,
	"上海": 40
};

/**
 * 从用户输入中获取数据，向aqiData中增加一条数据
 * 然后渲染aqi-list列表，增加新增的数据
 */
function addAqiData() {
	var city = document.querySelector('#aqi-city-input')
	,	value = document.querySelector('#aqi-value-input')
	,	strCity = city.value
	,	strValue = value.value

	if (strCity == '' && strValue == '') {
		alert('不允许为空')
		return;
	}

	aqiData[strCity] = strValue
}

function subAqiData(e) {
	var target = e.target
	if(target && target.innerHTML == "删除" ) {
		var city = target.parentNode.parentNode.firstElementChild.innerHTML
		delete aqiData[city]
	}
}

/**
 * 渲染aqi-table表格
 */
function renderAqiList() {
	var mainDom = document.querySelector('#aqi-table tbody')
    ,	resuleStr = ''
    for(var i in aqiData){
    	resuleStr += '<tr><td>'+i+'</td><td>'+ aqiData[i]
    		+ '</td><td><button>删除</button></td></tr>'
    }
    mainDom.innerHTML = resuleStr
}

/**
 * 点击add-btn时的处理逻辑
 * 获取用户输入，更新数据，并进行页面呈现的更新
 */
function addBtnHandle(e) {
  
  addAqiData(e);
  renderAqiList();
}

/**
 * 点击各个删除按钮的时候的处理逻辑
 * 获取哪个城市数据被删，删除数据，更新表格显示
 */
function delBtnHandle(e) {
  // do sth.
  e = window.event || e
  subAqiData(e)
  renderAqiList();
}

function init() {

  // 在这下面给add-btn绑定一个点击事件，点击时触发addBtnHandle函数

  // 想办法给aqi-table中的所有删除按钮绑定事件，触发delBtnHandle函数
  var btn = document.querySelector('#add-btn')
  ,	  mainBody = document.querySelector('#aqi-table tbody')

  btn.addEventListener('click',addBtnHandle,false)
  mainBody.addEventListener('click',delBtnHandle,false)

  renderAqiList()
}

window.onload = init