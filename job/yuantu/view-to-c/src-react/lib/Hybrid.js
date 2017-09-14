
/**
 * hybird
 * 与native同行基础
 * */

import JSBridge from './JSBridge'
import util from './util'

/**
  构建一个 Hybird  请求， 并可以获得返回值
  return send 方法返回一个  Promise 对象
**/

class Hybird {
  constructor(methodName, param){
    this.protocol = "hybrid://";
    this.nativeClassname = "jsbrige";
    this.sid = parseInt(Math.random() * 100000);
    this.methodName = methodName;
    this.param = param || {};

  }

  getURI(){
    if (util.getPlatform() === 'ios' && util.version.gt(3, 4, 9)) {
      return this.protocol + this.nativeClassname + "-" + this.sid + "/" + this.methodName + "?" + JSON.stringify(this.param);
    }
    return this.protocol + this.nativeClassname + ":" + this.sid + "/" + this.methodName + "?" + JSON.stringify(this.param);
  }

  send(){

    return new Promise((reslove, reject)=>{

      //监听一个 sid 事件
      JSBridge.once(this.sid, (data)=>{
        if(data && data.ret == "SUCCESS"){
          reslove(data)
        }else{
          reject(data)
        }
      });

      // let script = document.createElement("script");
      // script.src=this.getURI();
      // alert(script.src)
      // document.getElementsByTagName("head")[0].appendChild(script);
      //只能使用iframe才能被监听到
      let iframe = document.createElement('iframe');
      iframe.setAttribute('frameborder', '0');
      iframe.style.cssText = 'width:0;height:0;border:0;display:none;';

      iframe.setAttribute('src', this.getURI());
      document.body.appendChild(iframe);

    })
  }

}



module.exports = Hybird;
