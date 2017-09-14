# react 开发规范

* author：Saohui
    * react 开发规范，部分由 Saohui 编写
    * 如果发现问题可以，联系微信号： `w_z_w_520`

### react 架构模型
![](http://upload-images.jianshu.io/upload_images/604678-78348f70f6ad52c3.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

### 目录结构

```
- BaseComponent => 基础组件类的封装，对 React.Component 进行二次封装，包括 loading 机制，错误处理机制
- component => 公共组件的封装，一些常用的基础组件的封装（有一些老的页面组件也放在这，这是因为老版本没有 pageComponent，故放在了这）
- hoc => 用于自动刷新（手机端浏览器，返回后不能自动刷新）
- lib => 一些库函数，工具函数
- module => 基础模块，如：数据请求接口
- pageComponent => 页面组件，方便单页面进行组件化（当一个页面比较复杂的时候，需要进行组件化，但是这部分组件往往复用率比较低，这时候如果放到公共的组件库中，就会加大维护成本）
    - "pageName" => 页面的命名规范遵循和页面名称一致的一个文件夹，然后在里面进行新建 js 文件进行组件开发
```

### 项目相关工具

* `webpack`
* `less`
* `git`
* `nodejs`
* `babel`

### 环境、开发、调试

1. `git clone 项目地址`
2. `sudo npm install`
3. `npm run react-start`
4. `浏览器访问 http://localhost:8080/ ` Server指向 ./src-react 目录
5. `修改 host 文件使用，我们的域名进行本地开发测试`
    * 127.0.0.1	a.uat.yuantutech.com 
    * 127.0.0.1	a.daily.yuantutech.com 
    * 127.0.0.1	a.s.yuantutech.com 
    * 127.0.0.1	a.test.yuantutech.com
6. `把浏览器访问 localhost 改为 a.uat.yuantutech.com`

### 关于 git branch

##### 开发新功能

`git checkout -b daily/a.b.c`  升级b  比如当前线上版本号`1.0.0` 新功能的版本应该为 `1.1.0`.


##### 修改bug

`git checkout -b daily/a.b.c`  升级c  比如当前线上版本号`1.0.0` 新功能的版本应该为 `1.0.1`.

#### 切换完新版本后

* 修改 package.json 中的版本号
* `npm run dev`
* 然后就可以进行开发了


### 版本发布规则

#### 测试环境

* `npm run dev` // 打包测试环境代码
* `git add . `  // 添加到 git 本地缓存区
* `git commit -m 'xxx'  ` // 添加到 git 本地仓库
* `git push origin daily/x.x.x  ` // 把本地代码提交到服务器 
* 这时候就可以打开浏览器，`访问 uat.yuantutech.com/yuantu/x.x.x/xxx.html` 

### less 命名空间

* 当你新建一个页面后，需要进行样式的自定义时，需要在本 less 文件中包一个模块名称相同的 class 选择器
* 然后给页面组件的顶层的元素的 className 加上这个 class

例如：有一个页面叫 page-name.js

page-name.less
```
.page-name {
    // 这里面写你的样式
}
```

page-name.js
```
...
export default class PageName extends Smart... {
    render () {
        return <div className="page-name">
        </div>
    }
}
```

### 关于组件开发规范

在开发组件过程中，我们把代码分为两个区域
* render 区
* 业务逻辑 区

例如
```
...
export default class xxx extends Smart... {

    constructor ( props ) {
        super( props )
    }

    ... // 这边是业务逻辑区

    /***** render 区开始 *****/

    renderItem () {
        return <div>...</div>
    }

    render () {
        return <div className="page-name">
        </div>
    }
}
```

### 关于数据加载

* 数据加载，没有特殊情况统一把借口调用放入一下文件（jsonp）
    * UserCenter => 普通借口第阿勇