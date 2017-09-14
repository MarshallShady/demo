


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button,Menu, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'

export default class ShuttleBox extends React.Component{
	constructor(props) {
		super(props)
		this.state = {
			doctorLeft:props.doctorInfo.doctorLeft ||[],
			doctorRight:props.doctorInfo.doctorRight ||[],
			selectedMap:{},
			leftFilter:"",
			leftSelected:0,
			rightSelected:0
		}
	}

	componentWillReceiveProps(props){

		this.setState({
			doctorLeft:props.doctorInfo.doctorLeft||[],
			doctorRight:props.doctorInfo.doctorRight ||[]
		});

		if(!props.display){
			this.setState({
				selectedMap:{},
			})
		}
	}

	onchange(id){
		let selectedList = this.state.selectedList;
		let leftSelected = 0;
		let rightSelected = 0;
		let leftList = document.getElementsByClassName("doctorLeft");
		let rightList = document.getElementsByClassName("doctorRight")
		for(let i=0; i<leftList.length; i++){

			if(leftList[i].checked){
				leftSelected ++;
				selectedList[i]=true;
			}
		}
		for(let i=0; i<rightList.length; i++){
			if(rightList[i].checked){
				rightSelected ++;
			}
		}

		this.setState({
			leftSelected,
			rightSelected,
			selectedList
		})
	}
	update(){
		let doctorLeft = this.state.doctorLeft || [];
		let selectedList = [];
		for(let i=0;i<doctorLeft.length;i++) {
			for (let j = 0; j < doctorLeft[i].doctors.length; j++) {
				if (doctorLeft[i].doctors[j].planFlag) {
					let doctor={
						doctorNo:doctorLeft[i].doctors[j].doctorNo,
						doctorName: doctorLeft[i].doctors[j].doctorName,
						queueCode:doctorLeft[i].doctors[j].queueCode,
						queueName:doctorLeft[i].doctors[j].queueName,
						smallDeptCode:doctorLeft[i].doctors[j].smallDeptCode
					}
					selectedList.push(doctor)
				}
			}
		}
		this.props.add(JSON.stringify(selectedList));
	}

	detpChange(key,key2){
		let doctorLeft = this.state.doctorLeft || [];
		doctorLeft[key].doctors[key2].planFlag = !doctorLeft[key].doctors[key2].planFlag;
		this.setState({
			doctorLeft
		})
	}

	itemClick(id){
		let selectedMap = this.state.selectedMap;
		selectedMap[id] = !selectedMap[id]
		this.setState({
			selectedMap
		})
	}

	search(){
		this.setState({
			leftFilter: this.refs['search'].value
		})
	}
	getShuttleBox(){
		let {doctorLeft, doctorRight,leftFilter,selectedMap} = this.state;
		let leftList = doctorLeft.map((value,key)=>{
				let show = false;
				let tips= (value.doctors||[]).map((v,k)=>{
					if(v.planFlag){
						show = true;
					}
					return (
						<div className="leftDoctors-item" key={k}>
							<label title={v.smallDeptName}>
								<input type="checkbox" className="doctorLeftDoctors" checked={v.planFlag ? "checked" : ""} onChange={this.detpChange.bind(this,key,k)}  />
									{v.queueName}
									{
										//如果是专家医生这里需要显示科室名字
										v.priority == 0 ? `(${v.smallDeptName})` : null
									}
							</label>
						</div>
					)
				});
				if((value.doctorName||"").indexOf(leftFilter) != -1|| (value.py||"").indexOf(leftFilter) != -1|| (value.simplePY||"").indexOf(leftFilter) != -1) {
					return(
						<div  key={key} >
							<div className="item left-item" onClick={this.itemClick.bind(this,value.id)}>
								<span>{value.doctorName} ({value.doctorNo})</span>
							</div>
							{show || selectedMap[value.id]?
								<div className="leftDoctors">
									{tips}
								</div>:null
							}
						</div>
					)
				}
			})||[];

		let rightList = doctorRight.map((value,key)=>{
				let tips= (doctorRight[key].doctorDiagRooms||[]).map((v,k)=>{
					return (
						<span className="item-tips" title={v.smallDeptName}>
							<Tag key={k} >{v.queueName}</Tag>
						</span>
					)
				});
				return(
					<div key={key} className="item">
						<span>
							{doctorRight[key].doctorName}
							{
								//如果是专家医生这里需要显示科室名字
								doctorRight[key].priority == 0 ? `(${doctorRight[key].smallDeptName})` : null
							}
						</span>
						{tips}
					</div>
				)
			});

		return (
			<div className="doctorInfo" data-spm="scheduleShuttleBox">
				<div className="doctorList">
					<div className="headerBox">
						<div className="header-text">医生名称</div>
						<div className="header-search">
							<Input ref="search" onChange={this.search.bind(this)} type="search"/>
						</div>
					</div>
					<div className="list">
						{leftList||
						<div key={key} className="item">
							<span>暂无无可选医生</span>
						</div>}
					</div>
				</div>
				<div className="center">
					<div>
					<Button onClick={this.update.bind(this)} data-spm="1">保存</Button>
					</div>
				</div>
				<div className="doctorList">
					<div className="headerBox">
						<div className="header-text">医生名称</div>
					</div>

					<div className="list">
						{rightList}
					</div>
				</div>
			</div>
		)
	}
	render() {
		return (
			<div>
				{this.getShuttleBox()}
			</div>
		);
	}
}
