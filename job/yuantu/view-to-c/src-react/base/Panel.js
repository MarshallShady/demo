import React, {PropTypes} from 'react'
import {render} from 'react-dom'
import { Button} from 'react-piros'


export default class Panel extends React.Component{
	// static propTypes = {
	// 	title: PropTypes.string,
	// 	okText: PropTypes.string,
	// 	cancelText: PropTypes.string,
	// 	onClick: PropTypes.func
	// };
	constructor(props) {
		super(props);
	}
	render(){
		let {title,okText,cancelText,onClick} = this.props;
		return(
			<div className="content-panel">
				<div className="panel-title">{title}</div>
				<div className="panel-body">
					<Button  size="lg"  secondary={true} onClick={()=>{
						onClick&&onClick(true)
					}}>{okText || "确定"}</Button>
					<Button  size="lg"  secondary={true}onClick={()=>{
						onClick&&onClick(false)
					}}
					>{cancelText || "取消"}</Button>
				</div>
			</div>
		)
	}
}