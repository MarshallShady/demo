import React from 'react'
import {render} from 'react-dom'

export default class Loading extends React.Component{
	
	render(){
		let {display, text, style} = this.props;
		return(
			display ? <div className="loading-wrapper" style={this.props.style} >
			    <span className="loading"></span>
			    <span>{text != undefined ? text : "正在加载中..."}</span>
			</div> : null
		)
	}
}