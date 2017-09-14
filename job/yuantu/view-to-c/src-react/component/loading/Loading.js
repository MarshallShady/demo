
import React from 'react'
import util from '../../lib/util'

import './loading.less'

// let loadingExample = null;

export default class Loading extends React.Component {
  constructor(props){
    super(props)
  }
  render(){
    return this.props.display ? <div className="no-block-loading"></div> : null
  }

}
// //显示
// Loading.show = function(){
//   loadingExample && (loadingExample.show());
// }
// //隐藏
// Loading.hide = function(){
//   loadingExample && (loadingExample.hide());
// }