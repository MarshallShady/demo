
import React from 'react'
import SmartComponent from './SmartComponent'
import {Loading} from '../component/loading/index'
import Alert from '../component/alert/alert'
export default class SmartNoBlockComponent extends SmartComponent {

  onSendBefore(){
    this.setState({
      loading:true
    })
  }

  onComplete(){
    this.setState({
      loading:false
    })
  }

  onError(result){
    console.log(result) // ---
    //通用错误处理 比如未登录跳转等等
    if(!super.onError.call(this, result)){
      //success === undefined 说明没有缓存数据
      //没有缓存数据， 新数据又错误，需要更新页面为false
      if(this.state.success  === undefined){
        this.setState({
          success:false, 
          msg:"网络错误，请稍后再试"
        })
      }else{
        //有缓存数据，仅提示即可
        Alert.show("数据更新失败，请稍后再试");
      }
    }

    return true;
  }

  toRender() {
    let {loading, success} = this.state;
    // console.log(this);
    if (success === false) {
      return this.renderError();
    }

    if(success){
      return this.__render();
    }

    return null;
  }

}
