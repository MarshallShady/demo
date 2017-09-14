
import React from 'react'
import BlockLoading from './BlockLoading2'
import util from '../../lib/util'

export default class AsyncRequestLoading extends React.Component {
  constructor(props) {
    super(props);
    this.defaultText = props.text || "加载中...";
    this.state = {
      display:false,
      text:"加载中...",
      type:"loading"
    }
  }

  onSendBefore(result){
    this.count++;//计数+1
    this.setState({
      display:true,
      text:result && result.msg ? result.msg : this.defaultText,
      type:"loading"
    });

  }

  onSuccess(result){

    if(this.props.showSuccess){
      this.setState({
        text:result.msg,
        type:"success"
      });
      setTimeout(()=>{
        this.setState({
          display:false
        });
      }, Math.max(result.msg.length * 100, 2000));
    }else{
      this.setState({
        text:result.msg,
        type:"success",
        display:false
      });
    }
  }

  onError(result){
    if(this.props.showError){
      this.setState({
        text:result.msg,
        type:""
      });

      setTimeout(()=>{
        this.setState({
          display:false
        });
      }, Math.max(result.msg.length * 100, 2000));

    }else{
      this.setState({
        text:result.msg,
        type:"",
        display:false
      });
    }
  }

  render(){
    let {text,display, type} = this.state;
    /**
      type:loading,success,error,wraning
    */
    return <BlockLoading text={text} display={display} type={type} />
  }
}
