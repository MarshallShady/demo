
/**
 * 使用 Proxy 来说实现被废弃的 Object.observe()
 * 
 * @param {any} target 
 * @param {any} fnBind 
 * @returns 
 */
var bind = function ( target, fnBind ) {
	bind.targets = bind.targets || []
	var targets = bind.targets
	,	index = targets.indexOf( target )

	bind.fnBinds = bind.fnBinds || []
	var fnBinds = bind.fnBinds
	if( index == -1 ) {
		index = targets.length
		targets.push( target )
		fnBinds.push( [] )
	}
	var targetFnBinds = fnBinds[index]
	targetFnBinds.push( fnBind )

  bind.proxy = bind.proxy || new Proxy( target, {
    set: function ( target, prop, value ) {
			target[prop] = value
      for( var i = 0; i < targetFnBinds.length; i ++ ) {
        targetFnBinds[i].call( target )
      }
    }
  } )
  return bind.proxy
}

var person = {
  name: '12'
  ,age: '23'
}
var child = bind( person, function () {
  console.log( 'bind: ', this.name )
} )
person.name = 333
child.name = 444
console.log( person )
console.log( child )

/* 代码分割线 */

/**
 * 使用 es5 的 Object.defineProperty 特性 来实现 Object.observe()
 * 
 * @param {any} target 
 * @param {any} fnBind 
 * @returns 
 */
var bind = function ( target, fnBind ) {
	bind.targets = bind.targets || []
	bind.cloneTargets = bind.cloneTargets || []
	var targets = bind.targets
	, closeTargets = bind.cloneTargets
	,	index = targets.indexOf( target )

	bind.fnBinds = bind.fnBinds || []
	var fnBinds = bind.fnBinds
	if( index == -1 ) {
		index = targets.length
		targets.push( target )
		closeTargets.push( Object.assign( {}, target ) )
		fnBinds.push( [] )
	}
	var targetFnBinds = fnBinds[index]
	targetFnBinds.push( fnBind )

	for( var prop in target ) {
		Object.defineProperty( target, prop, {
			set: function ( val ) {
				closeTargets[index][prop] = val
				for( var i = 0; i < targetFnBinds.length; i ++ ) {
					targetFnBinds[i].call( target )
				}
			},
			get: function () {
				return closeTargets[index][prop]
			}
		} )
	}

  return target
}

var person = {
  name: '12'
  ,age: '23'
}
var child = bind( person, function () {
  console.log( 'bind: ', this.name )
} )
person.name = 333
child.name = 444
child.name = 555
console.log( person )
console.log( child )