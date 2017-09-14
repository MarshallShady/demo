
import React from 'react'
import {render} from 'react-dom'


class BlockLoading extends React.Component {

  render(){

    return this.props.display ? <div className="blockLoading">{this.props.text || "加载中..."}</div> : null
  }
}



export default class AjaxLoading extends React.Component{
  constructor(props) {
    super(props);
    this.count = 0;
    this.state = {
      display:false
    }
  }

  onSendBefore(){
    this.count++;
    this.setState({
      display:this.count > 0
    });

  }

  onComplete(){
    this.count--;
    this.setState({
      display:this.count > 0
    });
  }
  
  render(){
    return <BlockLoading display={this.state.display} text={this.props.text} />
  }
}




