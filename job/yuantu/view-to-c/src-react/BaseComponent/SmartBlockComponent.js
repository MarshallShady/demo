
import React from 'react'
import SmartComponent from './SmartComponent'
import {BlockLoading} from '../component/loading/index'

// 阻断性加载中
// 缓存数据仅作为占位使用，新数据获取失败，页面不能直接显示缓存数据
// 设置 state.loading = true 时候会出现，loading 提示信息为 loadingMsg 默认为 加载中...
export default class SmartBlockComponent extends SmartComponent {

  onSendBefore(){
    this.setState({
      loading: true
    })
  }

  onComplete(){
    this.setState({
      loading: false
    })
  }

  renderLoading(){
    return null
  }
  onError(result){

    this.setState({
      loading: false
    })

    //通用错误处理
    //通用处理错误返回 false 说明未处理，需要自己处理
    if(!super.onError.call(this, result)){
      //success === undefined 说明没有缓存数据
      //没有缓存数据， 新数据又错误，需要更新页面为false
      this.setState({
        success:false, 
        msg:result.msg || "网络错误，请稍后再试"
      });
    }

    return true;

  }

  toRender() {
    let { success, loading, loadingMsg } = this.state;

    setTimeout(() => {
      if ( loading ) { 
        BlockLoading.show( loadingMsg == '' || loadingMsg == 'undefined' || !loadingMsg ? false : loadingMsg )
      } else {
        BlockLoading.hide()
      }
    })

    if (success === false) {
      return this.renderError();
    }
    
    if(success){
      return this.__render();
    }
    
    return null;
  }

}
