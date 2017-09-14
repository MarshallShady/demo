import React from 'react'
import {render} from 'react-dom'

import {Switch,Button,Tag,message} from 'react-piros'
export default class QueueSwitch extends React.Component{
	constructor(props) {
		super(props);
	}
	onClick(){
		this.props.onClick&&this.props.onClick()
	}
	onChange(checked){
		this.props.onChange&&this.props.onChange(checked);
	}
	render(){
		let {smallDeptName, title, checked, btnText, priority} = this.props;

		return(
			<div className="queue-item" title={smallDeptName}>
				<div className="item-header">
					{title}
					{
						priority == 0 ? `(${smallDeptName})` : null
					}
				</div>
				<div className="item-content">
					<div className="left">
						<Switch checked={checked} onChange={this.onChange.bind(this)} data-spm="1"/>
					</div>
					<div className="right">
						<Button size="lg"  secondary={true} onClick={this.onClick.bind(this)} data-spm="2">
							{btnText}
						</Button>
					</div>
				</div>
			</div>
		)
	}
}
