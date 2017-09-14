import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select,Menu} from 'react-piros'


export default class DoctorCard extends React.Component{
	constructor(props) {
		super(props);
		this.value = props.value;
		this.defaultValue = props.defaultValue;
		this.state = {
			hover:false,
			select: props.defaultValue?-1:0
		}
	}
	onMouseOver(){
		this.setState({
			hover: true
		})
	}
	onMouseLeave(){
			this.setState({
				hover: false
			})
	}
	select(key){
		let {list} = this.props;
		this.setState({
			select:key
		})
		this.props.onChange&&this.props.onChange(list[key])
	}

	render(){
		let {hover,select} = this.state;
		let {list,title} = this.props;
		return(
			<div className={hover?"dropdown dropdown-action":"dropdown"} onMouseOver={this.onMouseOver.bind(this)}
			     onMouseLeave={this.onMouseLeave.bind(this)}
			>
				<h1>{(list[select]||{}).text||this.props.defaultValue||title}</h1>
					<div className="dropdown-item-wrapper">
						<ul>{
							list.length == 0 ? <li className="dropdown-item">无数据</li> :
								list.map((item, key)=> {
									return <li key={key} className="dropdown-item" onClick={this.select.bind(this, key)}
									           data-spm={"corpIdorArea" + item.value}>{item.text}</li>
								})
						}
						</ul>
					</div>
			</div>
		)
	}
}