/**
 构建一个 Hybird  请求， 并可以获得返回值
 return send 方法返回一个  Promise 对象
 **/

import JSBridge from './JSBridge'
import util from './util'

class Hybird2 {
  constructor(methodName, param) {
    this.protocol = "hybrid://";
    this.nativeClassname = "jsbrige";
    this.methodName = methodName || '';
    this.sid = parseInt(Math.random() * 100000);
    this.param = param || {};
  }

  getURI() {
    if (util.getPlatform() === 'ios' && util.version.gt(3, 4, 9)) {
      return this.protocol + this.nativeClassname + "-" + this.sid + "/" + this.methodName + "?" + JSON.stringify(this.param);
    }
    return this.protocol + this.nativeClassname + ":" + this.sid + "/" + this.methodName + "?" + JSON.stringify(this.param);
  }

  send() {
    return new Promise((reslove, reject) => {
      //监听一个 sid 事件
      JSBridge.once(this.sid, (data) => {
        if (data && data.ret == "SUCCESS") {
          alert(JSON.stringify(data));
          reslove(data)
        } else {
          reject(data)
        }
      });
      if (native) {
        native[this.methodName](this.getURI());
      } else {
        let iframe = document.createElement('iframe');
        iframe.setAttribute('frameborder', '0');
        iframe.style.cssText = 'width:0;height:0;border:0;display:none;';

        iframe.setAttribute('src', this.getURI());
        document.body.appendChild(iframe);
      }
    })
  }

}


module.exports = Hybird2;
