
import React from 'react'

let TYPES = {
  "loading":"loading-icon loading",
  "error":"loading-icon error",
  "success":"loading-icon success",
  "wraning":"loading-icon wraning"
}
export default class BlockLoading extends React.Component {

  render(){
    let display = this.props.display;
    let text = this.props.text;
    let type = this.props.type;
    return display ? <div className="loading-mask ">
    <div className="loading-wrapper">
      {TYPES[type] ? <div className={TYPES[type]}></div> : null}
      <div className="loading-msg">{text || "加载中..."}</div>
    </div>
  </div> : null
  }
}
