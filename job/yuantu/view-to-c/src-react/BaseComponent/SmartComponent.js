
import React from 'react'
import util from '../lib/util'


export default class SmartComponent extends React.Component {

  constructor( props ){
    super(props);
    this.state = {
      loading:false,
      msg:"",
      code:""
      // success:undefined //undefined 没有只  true 值正确  false值错误
    };
    this.__render = this.render;
    this.render = this.toRender;
  }

  //return  是否已经处理了错误
  onError(result){

    this.setState({
      loading:false,
      msg:result.msg,
      resultCode:result.resultCode
    });

    if(result && result.resultCode == 202){
      this.setState({success:false, msg:"请先登录"});
      util.goLogin();
      return true;
    }

    return false;
  }

  renderError(){
    let {msg} = this.state;
    return <div className="ui-tips center">{msg}</div>;
  }


  toRender() {
    let {loading, success} = this.state;

    if(loading){
      return this.renderLoading();
    }

    if (success === false) {
      return this.renderError();
    }

    if(success){
      return this.__render();
    }

    return null;
  }
}
