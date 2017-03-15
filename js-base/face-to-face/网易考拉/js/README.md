## js 中 bind 函数的应用

我模拟了一个js原生的bind，在Function.ptotoytpe.bind.js文件里

### 第一个场景 绑定函数
```
this.num = 9; 
var mymodule = {
  num: 81,
  getNum: function() { return this.num; }
};

module.getNum(); // 81

var getNum = module.getNum;
getNum(); // 9, 因为在这个例子中，"this"指向全局对象

// 创建一个'this'绑定到module的函数
var boundGetNum = getNum.bind(module);
boundGetNum(); // 81
```
### 第二个场景 预定义参数
```
function list() {
  return Array.prototype.slice.call(arguments);
}

var list1 = list(1, 2, 3); // [1, 2, 3]

// 预定义参数55
var leadingThirtysevenList = list.bind(undefined, 55);

var list2 = leadingThirtysevenList(); // [55]
var list3 = leadingThirtysevenList(1, 2, 3); // [55, 1, 2, 3]
```

### 第三种场景 回调函数的this绑定 
一般情况下 setTimeout、setInterval 函数的回调函数的 this 都是指向 window 或 global。 当使用类的方法时需要this指向类实例，就可以使用bind()将this绑定到回调函数来管理实例。
```
var obj = {
	name : '我有 this',
	num : 0,
	getNameDelay1000 : function(){
		setTimeout(this.getName.bind(this), 1000);
	},
	getName : function(){
		console.log(this.name)
	}
}
```

### 第四种场景 绑定函数作为构造函数
绑定函数也适用于使用new操作符来构造目标函数的实例。当使用绑定函数来构造实例，传入的 指向对象 this 会被忽略，但是传入的参数仍然可用。
```
function Point(x, y) {
  this.x = x;
  this.y = y;
}

Point.prototype.toString = function() { 
  return this.x + ',' + this.y; 
};

var p = new Point(1, 2);
p.toString(); // '1,2'


var emptyObj = {};
var YAxisPoint = Point.bind(emptyObj, 0/*x*/);
// 实现中的例子不支持,
// 原生bind支持:
var YAxisPoint = Point.bind(null, 0/*x*/);

var axisPoint = new YAxisPoint(5);
axisPoint.toString(); // '0,5'

axisPoint instanceof Point; // true
axisPoint instanceof YAxisPoint; // true
new Point(17, 42) instanceof YAxisPoint; // true
```
因为bind函数会使得Point和YAxisPoint共享原型，因此使用instanceof运算符判断时为true。具体可以看我模拟的源码。

### 第五种场景 捷径
bind()也可以为需要特定this值的函数创造捷径。

例如要将一个类数组对象转换为真正的数组，可能的例子如下：
```
var slice = Array.prototype.slice;

// ...

slice.call(arguments);

```
如果使用bind()的话，情况变得更简单：
```
var unboundSlice = Array.prototype.slice;
var slice = Function.prototype.call.bind(unboundSlice);

// ...

slice(arguments);

```

## 单例是什么？怎么实现？
单例模式是一种常用的软件设计模式。在它的核心结构中只包含一个被称为单例的特殊类。通过单例模式可以保证系统中一个类只有一个实例
* 可以用多种方法实现 *
```
// 最简单一种
var obj = {
	name : 'name',
	getName : function(){
		return this.name
	}
}

// 第二种闭包
var D = (function(){
	var singleObj = null
	return function(){
		if( singleObj ) return singleObj

		//代码实现
	}
}())

// 第三种直接挂载到函数身上
function D(){
	if(D.singleObj) return D.singleObj

	//代码实现
}
```

## 若有以下程序
```
//程序 A 部分

dom.onclick = function(){ //添加事件
	//程序 B 部分
}

//程序 C 部分
```
则请问 A、B、C 三部分代码的执行顺序？为什么？

执行顺序为 A -> C -> B
想知道为什么的可以看我写的一篇博客[《一张图看懂 JS 的事件机制》](http://www.cnblogs.com/iron-whale/p/jsEvents.html)

## 事件代理是什么？应用场景？有什么好处？
从名字我们就可以看出，代理处理事件的函数。简单来说就是要把添加给子元素的事件，加到父元素上，然后由事件对象 event.trget 去获取到真实的触发对象，处理相应的内容。例：
```
<ul>
	<li></li>
	<li></li>
	<li></li>
</ul>

<script>
	var ul = document.querySelector('ul')
	ul.addEventListener('click',function(e){
		e = e || window.event
		console.log( e.target ) // 点击li的时候，为li
	},false)
</script>
```
应用场景，比如电商平台的某个物品，需要记录点击次数的，你如果给每一个物品都添加点击事件就非常麻烦，且如果当你有动态添加物品的时候，就会导致有时候绑定的时候，且代码会更加麻烦肯定需要对物品流进行遍历然后给每一个子元素进行绑定，用事件委托的形式的话可以减少这些麻烦。

## Ajax 原生的流程是什么？
```
var xml = new XMLHttpRequest();// 新建 XMLHttpRequest 对象
	xml.onreadystatechange=state_Change;// 绑定监听函数
	xml.open("GET",url,true);// 发送内容 参数( 'get/post'/* 模式选择*/, url, true/*异步*/ false/*同步*/ )
	xml.send(null);//发送内容


  	function state_Change()
	{
		if (xml.readyState==4)
		{// 4 = "loaded"
			if (xml.status==200)
			{// 200 = OK
				// ...our code here...
			}
			else
			{
				alert("Problem retrieving XML data");
			}
		}
	}
```

## JSONP 是什么原理是什么？
HTML 中的 script 标签，有一个好处是不受同源的影响可以给引入外部文件，而且当把它的属性 type="text/javascript" 的时候，无论加载过来的文本是什么都会被解析成js代码且直接执行。所以利用这一个特性，出现了 jsonp 。

jsonp的原理比较简单
* 第一步 前端动态生成一个 script 设置好 type（因为默认为 text/javascript 所以不用设置），* 设置 url 并且把回调函数名称以参数的形式传给后台 *，然后动态添加到文档流中
* 第二步 就是让后台配合前端，以字符串的形式返回 js 代码，直接调用前端传给它的回调函数名称，并且把 data 数据直接以参数形式调用。

例：
```
var script = //...
script.src = 'http....?callBack=isJsonp'
document.body.append(script)
function isJsonp(data){
	console.log(data)
}


// 这时候文档流中就会有这么一个标签
<script src="http...?callBack=isJsonp"></script>


// 而返回的文本的内容这样既可
// 设返回的 json 为 { name : 'is jsonp'}
callBack({ name : 'is jsonp'});


// 后台可以这么写，假设为PHP
echo $_GET['callBack'].'({ name : 'is jsonp'});';
```

## JQ 的选择器选择 li 时和原生 js 的 getElementsByTagName 获取的区别？$(".clall") 返回的是一个什么？
JQ 返回的对象如果是一个数组，则是静态的数组，而 getElementsByTagName 获取的一定是一个类数组对象，且是动态的，当有新的dom生成后，会自动追加到后面，JQ返回的数组或对象里面添加了很多关于JQ封装的接口是原生所没有的。
