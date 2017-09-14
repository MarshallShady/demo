/**
  帮助组件链接ajax请求状态
*/
import React from 'react'

function LoadingManage(Component){
  return class LoadingManage extends React.Component {
    constructor(props) {
      super(props);
      this.count = 0;
      this.state = {
        loading:false
      }
    }

    onSendBefore(){
      this.count++;
      this.setState({
        loading:this.count > 0
      });
    }

    onComplete(){
      this.count--;
      this.setState({
        loading:this.count > 0
      });
    }

    render(){
      return <Component {...this.props} loading={this.state.loading}>{this.props.children}</Component>
    }
  }

}

export default LoadingManage;
