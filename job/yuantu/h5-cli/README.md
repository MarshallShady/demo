# 远图 h5（无线端）架构设计 —— 基于组件的单一入口，模块多版本共存软件的前端架构（类单页）
场景，因为远图是一家为医院提供服务的公司，有时候会碰到一个问题，当你开发一个页面的时候，不同医院可能会让你去改不同的东西，虽然大部分时候是一样的。

碰到这种只有部分页面，有特殊需求的场景，大部分人可能会采用配置文件形式，但是这样又会碰到几个问题

* 这个版本一直迭代下去之后，配置文件就会越来越大，因为每一个医院在不同版本上都有可能提出不同的要求
* 关键是你到后面，这个配置文件里面的内容你还不敢删，因为你不知道里面的东西在哪里被引用过
* 医院和模块使用配置文件的边界不清晰（这到可以使用规定配置文件的格式来解决）但是维护成本还是很大
* 实际代码变成很难看，很难维护因为肯定有一堆根据配置文件里面的东西来修改的业务逻辑，什么 if else。。。

所以基于以上问题我们就知道我们要解决三个问题：

* 我们必须非常清晰医院的边界在那里
* 如何为一家医院在原来页面的基础上进行定制化开发
* 如何共享标准功能的升级和迭代（基础页面迭代开发，要能被各医院定制化开发所共享）

首先介绍一下，我们基础的架构，在来讲满足以上需求的架构

## 基础架构
### 基础模型
![](https://front-images.oss-cn-hangzhou.aliyuncs.com/i4/dbc216720ddd7ff499d44406e4523ed8-733-475.png)

app.js 主要起到一个路由的功能

实际的模型中，还会有一些公共函数的引用，为了方便理解，这边就不画出来了

### 目录结构

```
* src
	* commons/
		* xxx.js
	* lib/
		* xxx.js
	* modules/
		* xxx.js
	* app.js
	* a.html
	* a.js
	* b.html
	* b.js
	* c.html
	* c.js
	* xxx.html
	* xxx.js
	* n.html
	* n.js
```

### 代码实现
##### app.js
```
import React from 'react'
import ReactDOM from 'react-dom'
import A from './a.js'
import B from './b.js'
import B from './c.js'
import D from './d.js'

class App extends React.Component {
  constructor(props) {
    super(props);
    let pathname = window.location.pathname;
    //截取页面名字 /a/b.html ==> b.html
    let pageName = pathname.match(/(\/[\w\-]+\.html$)/);
    pageName = pageName && pageName[1] ? pageName[1] : null;
    this.page = pageName ? pages[pageName] : null;
  }

  render() {
    let Page = this.page;
    /**
     * 阻断性加载中状态  BlockLoading
     * 非阻断性加载中状态  Loading
     * **/
    if (Page) {
      return <div>
        <Page />
      </div>
    } else {
      return <div>您访问的页面不存在{window.location.pathname}</div>
    }
  }
}

let pages = {};
function register(pathname, page) {
  if (!pages[pathname]) {
    pages[pathname] = page;
  } else {
    throw new Error(`"${pathname}" Already exist`);
  }
}

register( '/a.html', A )
register( '/b.html', B )
register( '/c.html', C )
register( '/d.html', D )

ReactDOM.render(
  <App />,
  document.getElementById('app')
)

```	

##### 其他页面
比如 a.js b.js 就按照正常的，写页面 js 那样去写就好了

## 多医院架构架构
### 基础模型

![](https://front-images.oss-cn-hangzhou.aliyuncs.com/i4/cd84afca237278faf68b47edd78c113c-702-388.png)

### 目录结构
![](https://front-images.oss-cn-hangzhou.aliyuncs.com/i4/8c103fccc4ed723fc8f0a78ebce07460-306-270.jpg)

首先我们会为建立一个 base/ 文件夹，里面存放着基础的标准版页面的代码（ a.js b.js c.js d.js ... ）然后会建立医院的 corpId/ 文件夹，它里面存放的也是页面代码，它继承于 base/ ，如果有医院有特殊需求，则会建立一个单独的文件进行特殊的定制化，从而覆盖了继承于 base/ 中的页面模块

然后我们会在入口文件，根据 corpId 不同去路由不同的文件夹

那么是如何进行继承的呢？

根据我们的需求我们知道，我们需要根据 url 参数中的 corpId 不同，访问不同的页面，也就是在入口文件（ app.js ）中，根据 corpId 不同加载不同的资源（不同文件夹），但是这样又会导致 app.js 文件太大，东西混杂难维护，**而且这样要实现继承是非常困难的**，所以我们需要把 app.js 医院文件夹路由功能和页面路由功能进行拆分

把医院文件夹路由放在 app.js 里，另外在医院文件夹里面建立 index.js 文件进行页面路由

在 base/ 底下的 index.js 会引用 base/ 底下的所有页面模块，并且暴露出接口，而在 corpId/ 下的 index.js 文件，首先会引用 base/index.js 文件（相当于继承）然后如果某个页面有定制化，则在此 corpId/ 底下建立 xxx.js ，然后在此 corpId/index.js 文件里 引用此页面模块，然后在替换从 base/index.js 继承来的模块内容，之后暴露出去

### 代码实现
##### app.js

首先对 app.js 入口文件进行拆分，把入口的 App 组件，和模块加载部分进行分离

* app.js // App 组件
* corpId/index.js // 医院模块加载的入口文件，负责加载依赖模块（如果需要特殊化的医院，则加载自己文件夹下的，否则加载 Base），并且暴露出接口

```
import util from 'util'
const query = util.query()
,   corpId = query.corpId || null
,   requirePath = corpId || 'Base'

const pages = require( './'+ requirePath +'/index.js' ) // 使用 require 实现动态加载

class App extends React.Component {
  constructor(props) {
    super(props);
    let pathname = window.location.pathname;
    //截取页面名字 /a/b.html ==> b.html
    let pageName = pathname.match(/(\/[\w\-]+\.html$)/);
    pageName = pageName && pageName[1] ? pageName[1] : null;
    this.page = pageName ? pages[pageName] : null;
  }

  render() {
    let Page = this.page;
    /**
     * 阻断性加载中状态  BlockLoading
     * 非阻断性加载中状态  Loading
     * **/
    if (Page) {
      return <div>
        <Loading />
        <BlockLoading />
        <Alert />
        <StatusBlock />
        <Page />
      </div>
    } else {
      return <div>您访问的页面不存在{window.location.pathname}</div>
    }
  }
}

render(<App />, document.getElementById("root"))
```

##### base/index.js
```
import A from './a.js'
import B from './b.js'
import B from './c.js'
import D from './d.js'
let pages = {};
function register(pathname, page) {
  if (!pages[pathname]) {
    pages[pathname] = page;
  } else {
    throw new Error(`"${pathname}" Already exist`);
  }
}

register( '/a.html', A )
register( '/b.html', B )
register( '/c.html', C )
register( '/d.html', D )

export default pages
```

##### corpId/index.js
```
import xxx from '../Base' // 使用标准版的模块
import xxx from './xxx' // 使用重写过的模块
// ...

let pages = {}
function register(pathname, page) {
  if (!pages[pathname]) {
    pages[pathname] = page;
  } else {
    throw new Error(`"${pathname}" Already exist`);
  }
}
register("xxx/xxx.html", xxx)
// ...

module.exports = pages
```
## 对文件进行拆分，实行按需加载

单页应用的代码拆分，一般按照一个视图一个代码片段进行拆分。故就可以很明确的知道，我们代码片段的最小单元是一个视图模块（如一个登入 sign-in.js）和它所依赖的模块。

但是我们业务比较特殊，就是有多医院，并且多医院分别有他们自己特殊的代码片段，所以为了减小 app.js 为入口打包后的文件大小，我们还得把 corpId/index.js 进行拆分。

因为公共函数、公共组件、公共函数库等被多视图模块依赖，为了利用浏览器缓存，同时减少文件大小，加快首屏渲染速度，我们还可以把公共函数进行单独打包。

故按照方案，我们最后打包的内容有：

```
* app.js => app.bundle.js
* corpId/index.js => corpId.bundle.js
* corpId/view.js => corpId/view.bundle.js
* ***/***.js => veoder.js
	* compontents/
	* BaseComponent/
	* lib/
	* module/
	* store/
```

然后利用 webpack 的 require.ensure 配合 require 进行异步文件打包，并异步加载：

我们已 app.js 为 demo 分别打包 corpId/index.js 为入口的文件

```
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
  , query = {}
  search = search.slice(1).split('&')
  search.forEach(function ( val ) {
    var temp = val.split('=')
    query[ temp[0] ] = decodeURIComponent( temp[1] )
  })
  return query
}
```