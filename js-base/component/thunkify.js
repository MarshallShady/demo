// Thunk 函数，可以拥有解决多嵌套问题！利用高阶函数特性，把参数进行颗粒化处理!把回掉给铺平

function Thunk(fn) {
	return function () {
		var args = Array.prototype.slice.call(arguments)
		,ctx = this
		return function (done) {
			var called = false
			args.push(function () {
				if(called) return // 保证回掉函数只执行一次
				called = true
				done.apply(this, arguments)
			})

			try{
				fn.apply(ctx, args)
			} catch (arr) {
				done(arr) // 把错误交给回掉函数来处理
			}
		}
	}
}

// Thunk 函数的真正威力在于，可以自动执行 Generator 函数
function run(fn) {
	var gen = fn();
	(function next(err, data) {
		var result = gen.next(data)
		if(result.done) return
		result.value(next)
	})()
}

// co 库中还写了 promise 的自动化执行，但是总的来说就是利用递归配合 Generator 函数来完成
// es6 中从语言层面，出现了来解决回掉嵌套调用的问题 async 函数！这是的之前的那些都成了过度品



