title: Containjs 说明
speaker: GW.iron
url: 
transition: zoomin

[slide]

# 简单模块加载工具实现
分享人：高少辉


[slide]

# 市面上现有的模块加载规范
## —— javaScript
* AMD 
* CMD
* CommonJs
* ES6 的 import export

[slide]

# 你有何的看法？
### 对现有的模块加载器~

[slide]

# 我的观点
## 只是单纯，觉得 AMD、CMD 写起来不爽

[slide]

* 语法方面：
	* define 太麻烦，喜欢 CommonJs 以文件为模块的语法
* 性能方面：
	* 新建 dom
	* 插入 dom
	* 异步加载了 js 文件
	* 执行 define 函数进行挂载

[slide]

# 想 法

[slide]

* 利用 regex 匹配出所依赖模块
* 利用 ajax 异步加载
* 利用 eval 动态解释执行程序
* 利用 function 来实现文件作用域

[slide]

```
// 设 ajax 加载的 “文件模块” 代码为 data
function module( require, module, exports ){
	eval( data )
}
```

[slide]

# 问 题
* eval 不知道会遇到什么问题
* require 加载模块时，不可以传入变量，只能传入静态字符串

[slide]

# 优化空间
* 让目录结构可配置
* 让入口文件可配置
* 让模块可预加载
* 模块生产完成，清除“代码字符串”，防止内存浪费
* ...

[slide]

# Thank you

