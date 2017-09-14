title: react-上拉刷新的组件封装
speaker: Theo Wang
url: https://www.baidu.com
transition: zoomin

[slide style="background-image: url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

# react-上拉刷新的组件封装

[slide style="background-image:url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

## 写一个上拉刷新需要什么？ 
----
* 监听滚动事件： {:&.fadeIn}
	* 页面滑动到最底下的时候需要进行重新加载
* 一个记录当前加载到第几页的变量
* 判断数据是否加载完，渲染底部为 loading 还是 “没有数据了”

[slide style="background-image:url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

* 我们会发现这些东西都不是业务逻辑 {:&.fadeIn}
* 但是却和业务逻辑混在了一起

[slide style="background-image:url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

### So 我们需要进行封装来解耦

[slide style="background-image:url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

## 我们需要
----
* 监听滚动事件： {:&.fadeIn}
	* 页面滑动到最底下的时候需要进行进行回调 {:&.fadeIn}
* 一个记录当前加载到第几页的变量
* 一个可以控制底部渲染底部为 loading 还是 “没有数据了”

[slide style="background-image:url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

### 最重要的是“把控制翻页的逻辑与业务逻辑进行解耦”

[slide style="background-image:url('https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1504700863960&di=9289d7f4790c3f76a85dfc49749a4275&imgtype=0&src=http%3A%2F%2Fpic.qiantucdn.com%2F58pic%2F17%2F98%2F15%2F12c58PICdbk_1024.jpg');"]

## 看代码
