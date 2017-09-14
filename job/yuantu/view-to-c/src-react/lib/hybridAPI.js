
import Hybrid from './Hybrid'

module.exports = {
  //testapi
  add:()=>{
    return new Hybrid("add", {a:1, b:2}).send()
  },
  errorTest:()=>{
    return new Hybrid("aaaaaaaaaa").send();
  },
  share:(title, text, url, imageUrl, isShowButton, isCallShare)=>{
    /**
      "isShowButton":true,  // 是否显示客户端的分享按钮
      "isCallShare":false , // 是否立即唤醒分享
      "title": result.data.title,
      "text": result.data.summary,
      "url":window.location.href,
      "imageUrl": "https://image.yuantutech.com/user/0c306d9a190c1bcde2d8f9ad31e29810-300-300.jpg"
    */
    return new Hybrid("share", {title, text, url, imageUrl, isShowButton, isCallShare}).send()
  },
  //返回上一个界面
  backView:( isReload )=>{
    return new Hybrid("backView", {isReload:isReload}).send()
  },
  //清除之前的viewController
  popToRootViewController:()=>{
    return new Hybrid("popToRootViewController", {}).send();
  },
  //携带参数返回上一个界面
  pushDataToParent:(autoBack, data)=>{
    return new Hybrid("pushDataToParent", {autoBack:autoBack, data:data}).send();
  },
  //设置页面参数
  setTitle:function(title){
    return new Hybrid("setTitle", {text:title}).send()
  },
  //获取 DeviceToken
  getDeviceToken:function () {
    return new Hybrid("getDeviceToken").send();
  },
  //清除缓存
  clearCache:function () {
    return new Hybrid("clearCache").send();
  },
  //上传图片组件
  callPhotoUpload:function ( crop ) {
    return new Hybrid("callPhotoUpload",{crop:crop}).send();
  },
  //唤起第三方导航控件
  skipNavigation:function(loaction){
    console.log(loaction)
    return new Hybrid("skipNavigation", {Coordinate:loaction}).send();
  },
  imageBrowser:function (current, urls) {
    return new Hybrid("imageBrowser", {urls:urls,current:current}).send()
  },
  //视频测试
  videoTest:function(){
    return new Hybrid("videoTest").send()
  },
  //设置未读消息条目
  setBadge:function (badge) {
    return new Hybrid("setBadge", {badge:badge}).send();
  },
  //打开页面
  openView:function (url, animation) {
    //bottom-in right-in
    return new Hybrid("openView", {url:url, animation:animation}).send()
  },
  //检查是否存在某个brige
  hasBrigeMethod:function (method) {
    return new Hybrid("hasBrigeMethod", {method:method}).send()
  },
  //加入视频对话页面
  joinVideoRoom:function (key, roomName, title, jumpURL, caller, callee, timeout) {
    return new Hybrid("joinVideoRoom", {
      key:key,//"c8fd086ac60c4e25b730c6369153304a",
      room:roomName,//"abc",
      title:title,//"网络诊间",
      timeout:timeout,//1000*30,
      jumpURL:jumpURL,//"http://www.baidu.com",
      caller:caller,//{name:"呼叫者",headImage:"http://image.yuantutech.com/user/e2725d04e88364af9fbfca1940a2011b-93-93.png"},//caller 呼叫的用户
      callee:callee,//{name:"被呼叫者",headImage:"http://image.yuantutech.com/user/36e4ec7d6de0d27bf517da602cab9778-93-93.png"}
    }).send()
  },
  pay:function (feeChannel, payData) {


    //支付宝
    // {
    //   feeChannel:1,
    //     payData:{
    //   "service":"alipay.wap.create.direct.pay.by.user",//支付宝接口名
    //     "partner":"2088711222690042", // 商户号
    //     "seller_id" :"2088711222690042", //  商户id
    //     "payment_type":"1", //
    //     "notify_url":"http://api.daily.yuantutech.com/user-web/restapi/common/ali/payNotify", //后端回调地址
    //     "return_url":"http://api.daily.yuantutech.com/user-web/restapi/common/ali/payReturn",// 没有的，前端自己拼装
    //     "out_trade_no":parseInt(Math.random()*100000), //商户订单号  用此订单查询状态
    //     "subject":"浙江远图充值", //中文字符串
    //     "body":"医院充值",
    //     "total_fee":"1" // 充值
    // }

    //微信支付
    // {
    //   "feeChannel": 2,
    //   "payData": {
    //   "mch_id": "1279981801",
    //     "timeStamp": "1477387885",
    //     "signType": "MD5",
    //     "notify_url": "http://api.uat2.yuantutech.com/user-web/restapi/common/wx/payNotify",
    //     "appid": "wxe9062380f1a04582",
    //     "nonceStr": "SU1477387885",
    //     "partner": "1279981801",
    //     "prepayId": "wx20161025173125b60ac107e60087819294",
    //     "paySign": "FCFAAC658A0A41E3528EADA4D6A35463"
    // }
    return new Hybrid("pay", {feeChannel:feeChannel, payData:payData}).send();
  },
  //用户评价功能，弹出引导评分窗口

  guide: function(day) {
    return new Hybrid("guide", {day}).send();
  },
  callCar: function(lat, lon, corpName, corpAddress) {
    return new Hybrid("callCar", {lat, lon, corpName, corpAddress}).send();
  },

  // 禁用掉页面的下拉刷新
  banRefresh: function() {
    return new Hybrid("banRefresh").send()
  }
}
