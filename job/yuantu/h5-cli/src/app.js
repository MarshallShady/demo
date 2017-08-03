import React, {Component} from 'react'
import ReactDOM from 'react-dom'

var setPages = function () {}

// 放在外面包着，首屏加载 loading
class App extends Component {
  constructor ( props ) {
    super( props )
    this.state = {
      pages: null
    }
    setPages = this.setPages.bind( this )
  }
  setPages ( pages ){
    this.setState({
      pages: pages
    })
  }
  componentDidMount () {
    
  }

  render () {
    const { pages } = this.state
    return <div>
      <h1>
        访问的页面是：{ 
        pages ? pages.default.name
        : 'loading'
      }</h1>
    </div>
  }
}

ReactDOM.render(
  <App />,
  document.getElementById('app')
)


new Promise(function ( resolve, reject ) {

  // 进行医院注册
  var corpId = UtilQuery().corpId || 'base'
  switch ( corpId ) {
    case '161': require.ensure( [], function () {
      var pages = require( './161/index.js' )
      resolve( pages )
    }, '161')
    break
    case '162': require.ensure( [], function () {
      var pages = require( './162/index.js' )
      resolve( pages )
    }, '162')
    break
    default: require.ensure( [], function () { // 不能直接使用 async 否则会不进行单独文件打包。直接使用 require 则可以，但是这样会导致没有第三个 参数
      ;(async function () {
        var pages = require( './base/index.js' )
        , page = await pages.default()
        resolve( page )
      })()
    }, 'base')
  }
}).then(function ( pages ) {
  setPages( pages )
})

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