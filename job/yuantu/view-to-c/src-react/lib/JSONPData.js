import AsyncData from './AsyncData'
import JSONP from './JSONP'

//jsonp的异步请求
//增加了一个 SUCCESS 事件
export default class JSONPAsyncData extends AsyncData {

  //增加一个异步success
  SUCCESS = "success"
  ERROR = "error2"

  constructor(url, param) {

    param = param || {};
    param.t = parseInt(Math.random() * 100000);

    super(url, param);


  }

  subscribe(observer) {
    observer.onSendBefore && this.on(this.SEND_BEFORE, function () {
      observer.onSendBefore.apply(observer, arguments)
    });

    observer.onComplete && this.on(this.COMPLETE, function () {
      observer.onComplete.apply(observer, arguments)
    });

    observer.onIOError && this.on(this.IO_ERROR, function () {
      observer.onIOError.apply(observer, arguments)
    });

    observer.onSuccess && this.on(this.SUCCESS, function () {
      observer.onSuccess.apply(observer, arguments)
    });

    observer.onError && this.on(this.ERROR, function () {
      observer.onError.apply(observer, arguments)
    });

    return this;
  }

  fetch() {

    return new Promise((reslove, reject) => {
      //监听异步完成事件
      this.on(this.COMPLETE, (result) => {
        //验证返回数据是否正确
        if (result) {
          this.emit(this.SUCCESS, result);
          reslove(result)
        } else {
          console.log("请求无效，ioerror会处理")
        }
      });

      //把io错误
      this.on(this.IO_ERROR, (e) => {
        // console.log("this.IO_ERROR")
        this.emit(this.COMPLETE, {msg: "网络错误,请稍后再试"});
        // this.emit(this.ERROR, {msg:"网络错误，请稍后再试", resultCode:404})
        reject({msg: "网络错误，请稍后再试", resultCode: 404})
      })

      //发送请求之前
      this.emit(this.SEND_BEFORE, this.url, this.param);

      this.loadData().then((result) => {
        //完成
        this.emit(this.COMPLETE, result, this.param);
      }).catch((e) => {
        //错误
        this.emit(this.IO_ERROR, e);
      });
    })

  }

  loadData() {
    return new Promise((reslove, reject) => {
      var param = {};

      for (var key in this.param) {

        if (this.param[key] !== "" && this.param[key] !== "undefined" && this.param[key] !== undefined && this.param[key] !== null) {
          param[key] = this.param[key]
        }
      }
      
      JSONP(this.url, param, reslove, () => {
        reject({msg: "请求错误"})
      });
    });
  }


  onSuccess(callback) {
    this.on(this.SUCCESS, callback);
    return this;
  }

  onError(callback) {
    this.on(this.ERROR, callback);
    return this;
  }

}
