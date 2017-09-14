
/**
 * hybird
 * 与native同行基础
 * */

import events from 'events'


let JSBrige = new events.EventEmitter();
//负责与native通信
JSBrige.windvane =  {
  //success
  //native会调用这个两个函数
  onSuccess:function(sid, data){
    JSBrige.emit(sid, data);
  },
  //failure
  onFailure:function(sid, data){
    JSBrige.emit(sid, data);
  }
};

/**
  兼容以前
  提供全局函数

  lib.windvane.onSuccess
  lib.windvane.onFailure

**/

window.lib = JSBrige;

module.exports = JSBrige;
