
/**
  处理webview的生命周期
  
  http://gitlab.yuantutech.com/yuantu/h5-cli/wikis/h5-page-life

  let life new WebViewLifeCycle()
**/

import JSBridge from './JSBridge'

let EVER = "onever"
let READY = "onready"
let ACTIVEATION = "onactivation"
let PAUSE = "onpause"

class WebViewLifeCycle {

  constructor() {

    var sid = parseInt(Math.random() * 100000);

    JSBridge.on("0", ( data )=>{
      /**
        data = {
          type:"event",
          name:"ACTIVEATION"
        }
      */ 
      let eventType = null;     
      if( data && data.type == "event" ){
          eventType = data.name;
      }
      console.log(sid)
      //READY ACTIVEATION PAUSE
      if(eventType){
        JSBridge.emit(sid, data);
        // JSBridge.emit(eventType, data);
      }
    });

    JSBridge.on( READY, ( result )=>{
      this.onPageReady(result);
    });

    JSBridge.on( ACTIVEATION, (result)=>{
      this.onActivation(result);
    });

    JSBridge.on( PAUSE, (result)=>{
      this.onPause(result);
    });

    //sid
    JSBridge.on(sid, (data)=>{
      /**
        data = {
          type:"event",
          name:"ACTIVEATION"
        }
      */
      //READY ACTIVEATION PAUSE
      if(data.name == READY){
        this.onPageReady(data)
        // JSBridge.emit(data.name, data);

      }else if(data.name == ACTIVEATION){
        this.onActivation(data)
      }else if(data.name == PAUSE){
        this.onPause(data);
      }else{
        console.log("not "+ data.name);
      }
    })
  }
  //页面已准备好
  onPageReady(){
    console.log("onPageReady")
  }
  //页面被激活
  onActivation(){
    console.log("onActivation")
  }
  //页面退到后台
  onPause(){
    console.log("onPause")
  }
}

module.exports = WebViewLifeCycle;
