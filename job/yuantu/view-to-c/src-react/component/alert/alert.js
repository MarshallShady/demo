
import React from 'react'
import './alert.less'

let alertExample = null;

export default class Alert extends React.Component {
  constructor(props){
    super(props)
    this.state = {
      text:"",
      display:false
    }

    alertExample = this;
  }
  show(text){
    this.setState({
      text:text,
      display:true
    })
  }
  hide(){
    this.setState({
      display:false
    })
  }
  render(){
    return this.state.display ? <div className="ui-alert" onClick={this.hide.bind(this)}><div className="text">{this.state.text}</div></div> : null
  }
}

Alert.timeoutid = null
//显示
Alert.show = function( text, timeout ){
  if(alertExample){
    Alert.timeoutid && clearInterval(Alert.timeoutid);
    alertExample.show(text);
    Alert.timeoutid = setTimeout(()=>{
      this.hide()
    }, timeout || text.length * 500);
  }
}

Alert.error = function(text, timeout){
  Alert.show(text ,timeout)
}
//隐藏
Alert.hide = function(){
  alertExample && (alertExample.hide());
}
