/*
 * 飞机速度 360deg/6 = 60deg
 * 能量每秒 -5%
 * 太阳补充 +2%
 */

var Control = (function (factoryAircraft) {
	var Aircraft = factoryAircraft()//生产飞机类
	,	taskQueue = [ // 任务队列
		// {
		// 	id : null
		// 	,instruction : 'add'
		// 	,sendState : true // success：true；error：false
		// }
		// ,{
		// 	id : 1
		// 	,instruction : 'start' // 'start' 'stop' 'delete'
		// 	,sendState : true // success：true；error：false
		// }
	]
	,	answer = [] // 格式与指令一致，指令成功执行后，飞船反馈的消息

	setInterval(function () { // 测试区
		Aircraft.receiver(taskQueue,answer)
		Control.consoleLog( answer )
	},2500)
	
	var Control = {

		// 控制者发送指令
		// sendInstruction(instruction,[id])
		// instruction == 'start'/'stop'/'delete' 需要 id
		sendInstruction : function (instruction, id) {
			var o = {}
			o.instruction = this.isInstruction(instruction) && instruction
			o.id = id?id:null
			o.sendState = this.isSendSuccess()
			taskQueue.push(o)
		}
		,isInstruction : function (data) {
			return (data == 'start'
					|| data == 'stop'
					|| data == 'delete'
					|| data == 'add')
		}
		,isSendSuccess : function () {
			var t = Math.random()
			return t > 0.3 ? true : false
		}
		,consoleLog : (function () {
			var domConsole = document.querySelector('.console-log')
			,	template = {
				add: '新建飞机'
				,start: '启动飞机'
				,stop: '停止飞机'
				,delete: '飞机自毁'
			}

			function updataDom(val){
				domConsole.appendChild(creatDom(val))
				domConsole.scrollTop = domConsole.scrollHeight
			}
			function creatDom(data) {
				var dom = document.createElement('p')
				,	id = data.id
				,	isSuccess = data.sendState
				,	temp = getDate()

				temp += id?id+'号':''
				temp += template[data.instruction]
				temp += isSuccess?'成功':'失败'

				dom.className = isSuccess?'success':'error'
				dom.innerHTML = temp
				return dom
			}
			function getDate() {
				var date    = new Date
				,	year    = date.getFullYear()
				,	mouth   = date.getMonth() + 1
				,	day     = date.getDate()
				,	hours   = date.getHours()
				,	minutes = date.getMinutes()
				,	seconds = date.getSeconds()
				,	r       = year+'-'+mouth+'-'+day
							+' '+hours+':'+minutes+':'+seconds+''
				return r
			}

			return function consoleLog(datas) {
				if (datas.length == 0) { return }
				var val = datas.shift()
				updataDom(val)
				
				// 回调直到执行完，应答序列
				consoleLog(datas)
			}
		}())
	}
	return Control
}(function (){// 飞机类
	var airs = {
		//id => 
		//1: new Aircraft(),
		length: 0
	}
	,	universe = document.querySelector('.main-content .content')
	,	eleControl = document.querySelector('.control-content')

	function Aircraft(id) {
		this != window && this.init(id)
	}
	Aircraft.prototype.setContent = function () {
		this.content = {
			id: null // 从 1 开始 最多 4
				,_status: false // true 表示起飞 默认停止
				,_speed: '60deg'
				,energy: 100 // 默认 100%
				,_energyConsume: 5 // 能量消耗值
				,_energySunSupplement: 2 // 太阳能量补充值
				,time: 1000 // ms
				,domAir: null // 飞机 真实DOM引用
				,domCtrl: null // 操控台
				,domEShowNun: null // 能量显示
				,domEShowBg: null // 能量显示
		}
	}

	Aircraft.prototype.init = function (id) {
		this.setContent()

		this.content.id = id
		this.createEleAir()
		this.createEleCtrl()
		this.takeOff()
	}


	/*******DOM 操作*******/
	Aircraft.prototype.createEleCtrl = function () {
		var dom = document.createElement('div')
		,	id = this.content.id
		dom.innerHTML = '<h3>'+ id +'号飞机</h3>'
					+	'<button>开始</button>'
					+	'<button>结束</button>'
					+	'<button>删除</button>'
		dom.setAttribute('class','item')
		dom.setAttribute('data-id',id)

		eleControl.appendChild(dom)
		this.content.domCtrl = dom
	}
	Aircraft.prototype.createEleAir = function () {
		var dom = document.createElement('div')
		,	content = this.content
		,	id = content.id
		,	energy = this.getEnergy()
		dom.innerHTML = '<div class="air-body">'
					+		'<div class="energy-bg"></div>'
					+		'<div class="energy-val">'
					+			'<h3>'+ id +'：<span>'+ energy +'</span>%</h3>'
					+		'</div>'
					+	'</div>'
		dom.setAttribute('class','air air-1')

		universe.appendChild(dom)
		content.domAir = dom

		content.domEShowNun = dom.querySelector('.energy-val span')
		content.domEShowBg = dom.querySelector('.energy-bg')
	}

	// 自爆程序
	Aircraft.prototype.removeAir = function () {
		var id = this.content.id

		eleControl.removeChild(this.content.domCtrl)
		universe.removeChild(this.content.domAir)

		delete airs[ id ]
	}


	/*******飞船驱动、停止*******/
	Aircraft.prototype.takeOff = function () {
		this.content._status = true
		this.content.domAir.className = 'air air-1 take-off'
	}
	Aircraft.prototype.land = function () {
		this.content._status = false
		this.content.domAir.className = 'air air-1'
	}

	Aircraft.prototype.getSpeed = function () {
		return this.content._speed
	}


	/*******能量控制*******/
	// 能量补充
	Aircraft.prototype.supplementEnergy = function () {
		var content = this.content

		content.energy += content._energySunSupplement
		if(content.energy > 100) {
			content.energy = 100
		}
		return content.energy
	}
	// 能量消耗
	Aircraft.prototype.consumeEnergy = function () {
		var content = this.content

		content.energy -= content._energyConsume
		if(content.energy < 0) {
			content.energy = 0
			this.land()
		}
		return content.energy
	}
	Aircraft.prototype.showEnergy = function () {
		var content = this.content
		,	energy = this.getEnergy()
		content.domEShowNun.innerHTML = energy
		content.domEShowBg.style.width = energy + '%'
	}
	Aircraft.prototype.getEnergy = function () {
		return this.content.energy
	}
	Aircraft.prototype.updataEnergy = function () {
		if(this.content._status){ this.consumeEnergy() }
		this.supplementEnergy()
		this.showEnergy()
	}



	/*******飞船控制*******/
	Aircraft.addAir = function () {
		var len = airs.length
		if( len > 4 ) return false
		for(var i = 1; i <= 4; i++){
			if( !airs[i] ){
				airs[i] = new Aircraft(i)
				airs.length++
				return true
			}
		}
		return false
	}

	// 作为飞船的接收机
	Aircraft.receiver = function (tasks,answer) {
		if(tasks.length == 0) return 
		var val = tasks.shift()
		,	id = val.id
		,	instruction = val.instruction
		,	isSuccess = val.sendState
		
		if(isSuccess)
			switch(val.instruction){
				case 'add': 
					val.sendState = Aircraft.addAir()
					break;
				case 'start': 
					airs[id].takeOff()
					break;
				case 'stop': 
					airs[id].land()
					break;
				case 'delete': 
					airs[id].removeAir()
					break;
			}
		answer.push(val)

		// 把指令队列执行完整为止
		Aircraft.receiver(tasks,answer)
	}

	Aircraft.timer = setInterval(function () {
		for(var i in airs){
			if(i!='length'){
				airs[i].updataEnergy()
			}
		}
	},1000)

	return Aircraft
}))

/******DOM 事件添加区*******/
document.querySelector('.add-air').onclick = function () {
	Control.sendInstruction('add')
}

document.querySelector('.control-content').onclick = function (event) {
	// Control.sendInstruction('stop',1)
	var event = event || window.event
	,	target = event.target

	if ( target.nodeName.toLowerCase() == 'button' ) {
		var parent = target.parentNode
		,	id = parent.getAttribute('data-id')
		,	val = target.innerHTML
		switch(val){
			case '开始':
				Control.sendInstruction('start',id)
				break;
			case '结束':
				Control.sendInstruction('stop',id)
				break;
			case '删除':
				Control.sendInstruction('delete',id)
				break;
		}
	}
}