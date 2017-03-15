// 若浏览器已经支持，则返回原生对象，否则自己编写
Function.prototype.bind = Function.prototype.bind || function(othThis) {
	var thatArgs = Array.prototype.slice.call( arguments, 1 ),
		that = this
	function T(){}//用于原型继承，防止直接引用被修改原型
	function f() {
		var thisArgs = Array.prototype.slice.call( arguments )
		thatArgs = thatArgs.concat( thisArgs )
		this === window ? 
		that.apply( othThis, thatArgs ) : that.apply( this, thatArgs )
	}
	T.prototype = that.prototype
	f.prototype = new T
	return f
}