
/**
 * 进行模块的注册
 * 
 * @export
 * @returns 返回一个 Promise 用于 await 语法，实际返回 注册成功的模块
 */
export default function pages() {
  return new Promise(function ( resolve, reject ) {
    let pageName = UtilQuery().pageName
    pageName == 'a.js' && require.ensure( [], function () {
      const a = require( './a.js' )
      resolve( a )
    }, 'base/a')

    pageName == 'b.js' && require.ensure( [], function () {
      const b = require( './b.js' )
      resolve( b )
    }, 'base/b')
  })
}

function UtilQuery () {
  var search = window.location.search
  ,	query = {}
  search = search.slice(1).split('&')
  search.forEach(function ( val ) {
    var temp = val.split('=')
    query[ temp[0] ] = decodeURIComponent( temp[1] )
  })
  return query
}


// import a from './a'
// console.log( 'base req a', a)
// export default {
//   name: 'base'
// }