import AsyncData from './AsyncData'
import JSONP from './JSONP'
import H5_VERSION from '../../h5-version';
import util from '../lib/util';
import 'isomorphic-fetch';
/**
 * JSONP('http://api.flickr.com/services/feeds/photos_public.gne',{'id':'12389944@N03','format':'json'},'jsoncallback',function(json){
 *  document.getElementById('flickrPic').innerHTML = '<p>Flickr Pic:</p><img src="'+json.items[0].media.m+'">';
 * });
 *
 * */

const isInYuantuApp = util.isInYuantuApp();

//jsonp的异步请求
//增加了一个 SUCCESS 事件
export default class JSONPAsyncData extends AsyncData {

  //增加一个异步success
  SUCCESS = "success"
  ERROR = "error2"

  constructor(url, param, timeout) {

    param = param || {};
    param.t = parseInt(Math.random() * 100000);

    param.invokerChannel = 'H5';
    param.invokerDeviceType = isInYuantuApp ? 'yuantuApp' : (util.isWeixin() ? 'weixin' : 'others');
    param.invokerAppVersion = H5_VERSION;

    super(url, param);
    this.url = url;
    this.timeout = timeout || '';
    this.JSONP = JSONP;
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
  isSuccess(result){
    return result && result.success;
  }
  fetch() {
    return new Promise((reslove, reject) => {
      //监听异步完成事件
      this.on(this.COMPLETE, (result) => {
        //验证返回数据是否正确
        if (this.isSuccess(result)) {
          this.emit(this.SUCCESS, result);
          reslove(result)
        }else{
          this.emit(this.ERROR, result);
          reject(result)
        }
      });

      //把io错误
      this.on(this.IO_ERROR, (e) => {
        console.log(e);
        // console.log("this.IO_ERROR")
        this.emit(this.COMPLETE, e.resultCode == -100 ? e : {msg: "网络错误,请稍后再试"});
        // this.emit(this.ERROR, {msg:"网络错误，请稍后再试", resultCode:404})
        //JSONP("https://msgsend.yuantutech.com/frontgatewaymsg/sendMsg.do", {msg: (JSON.stringify(e) + "报错url" + this.url), templateId: 10});
        reject({msg: "网络错误，请稍后再试", resultCode: 404})
      })

      //发送请求之前
      this.emit(this.SEND_BEFORE, this.url, this.param);

      //当有延时，调用Promise.race
      if (this.timeout) {
        const abortPromise = new Promise((resolve, reject) => {
          setTimeout(() => {
            reject({msg: "接口请求超时", resultCode: -100});
          }, this.timeout);
        });

        Promise.race([abortPromise, this.loadData()]).then((result) => {
          //完成

          //(!result.success) && JSONP("https://msgsend.yuantutech.com/frontgatewaymsg/sendMsg.do", {msg: (JSON.stringify(result) + "报错url" + this.url), templateId: 10});
          this.emit(this.COMPLETE, result, this.param);
        }).catch((e) => {
          //错误
          //fetch(`https://msgsend.yuantutech.com/frontgatewaymsg/sendMsg.do?msg=${JSON.stringify(e)}`);
          //JSONP("https://msgsend.yuantutech.com/frontgatewaymsg/sendMsg.do", {msg: (JSON.stringify(e) + "报错url" + this.url), templateId: 10});
          this.emit(this.IO_ERROR, e);
        });
      } else {
        this.loadData().then((result) => {
          //完成
          this.emit(this.COMPLETE, result, this.param);
        }).catch((e) => {
          //错误
          this.emit(this.IO_ERROR, e);
        });
      }
    });
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
