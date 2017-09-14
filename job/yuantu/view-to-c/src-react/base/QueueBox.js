


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button,Menu, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'

import QueueCenter from '../../module/QueueCenter';
import "../../css/base/QueueBox.less"

export default class QueueBox extends React.Component{
	constructor(props) {

		super(props);
		this.state = {
			count: 0,
			queueMap:new Map(),
			filter:""
		};

	}

	componentDidMount() {

	}


	componentWillReceiveProps(props) {
		var {queueMap} = this.state;
		if(this.props.display&&!props.display){
			for (var [key, value] of queueMap) {
				if(value){
					this.refs[key].checked = false;
				}
			}
			queueMap.clear()
			this.setState({
				queueMap,
				count:0
			})
		}

	}



	onChange(queueCode){
		var {count,queueMap} = this.state;
		if(this.refs[queueCode].checked){
			count++;
			queueMap.set(queueCode,true)
		}else{
			count--;
			queueMap.set(queueCode,false)
		}
		this.setState({
			count,
			queueMap
		})
		this.setState({
			queueMap
		})
	}

	submit(){
		var {queueMap} = this.state;
		for (var [key, value] of queueMap) {
			if(value){
				this.props.saveDoctorInfo(key)
				this.refs[key].checked = false;
			}
		}
		queueMap.clear();
		this.setState({
			queueMap,
			count:0
		})
		this.props.setDisplay(false)
	}
	onInputChange(){
		this.setState({filter : this.refs['search'].value})

	}

	render(){
		var {list} = this.props;
		var {count,filter} = this.state;
		return (
			<div className="queue-box">
				<div className="box">
					<div className="headerBox">
						<div className="header-text">
							<p>{"队列名称"}</p>
							<p>{count}/{list.length}{"队列"}</p>
						</div>
						<div className="header-search">
							<Input type="search" ref="search" onChange={this.onInputChange.bind(this)} placeholder={"输入队列名查询"}/>
						</div>
					</div>
					<div className="bodyBox">
						<ul>
							{
								list.map((item,key)=>{
									if(item.queueName.includes(filter) || item.py.includes(filter) || item.simplePY.includes(filter)) {
										return (
											<li key={key} className="boxItem">

													<span>
														<label>
															<input type="checkbox"
																ref={item.queueCode}
																onChange={this.onChange.bind(this, item.queueCode)}
													       value={item.queueCode}
														  /> {item.queueName} {item.smallDeptName ? `(${item.smallDeptName})` : null}
														</label>
													</span>

											</li>
										)
									}
								})
							}
						</ul>
					</div>
				</div>
				<div className="submit">
				<Button onClick={this.submit.bind(this)}>确认增加</Button>
				</div>
			</div>
		)
	}

}
