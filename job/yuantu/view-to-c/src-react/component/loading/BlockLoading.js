
import React from 'react'

let loadingExample = null;

export default class BlockLoading extends React.Component {
  constructor(props){
    super(props)
    this.state = {
      display:false,
      text:"加载中..."
    }

    loadingExample = this;
  }
  show(text){
    this.setState({
      display:true,
      text:text
    })
  }
  hide(){
    this.setState({
      display:false
    })
  }
  render(){
    return this.state.display ? <div className="loading-mask ">
    <div className="loading-wrapper">
      <div className="loading-icon loading"></div>
      <div className="loading-msg">{this.state.text || "加载中..."}</div>
    </div>
  </div> : null
  }
}
//显示
BlockLoading.show = function(text){
  loadingExample && (loadingExample.show(text));
}
//隐藏
BlockLoading.hide = function(){
  loadingExample && (loadingExample.hide());
}