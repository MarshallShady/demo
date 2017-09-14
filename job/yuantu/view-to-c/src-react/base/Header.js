import React, {PropTypes} from 'react'
import {render} from 'react-dom'
import { Button} from 'react-piros'
import Dropdown from './Dropdown'
import QueueCenter from '../../module/QueueCenter'
import HeaderText from './HeaderText'
let logo = require('../../image/c531db90d5ee7c3b26a7fbf3d5b1a9ae-32-32.png')
export default class Header extends React.Component{

	constructor(props) {
		super(props);
		this.state={
			userInfo:{
				// corpIds:["261", "332"]
			},
			hover:false,
			areaList:[],
			hospitalList:[],
			corpId:null,
			area:null
		}

		this.corpId = localStorage.getItem("defaultCorpId");
		this.area = localStorage.getItem("defaultArea");
		this.corpName = localStorage.getItem("defaultCorpName");
		this.areaName = localStorage.getItem("defaultAreaName");

	}
	componentDidMount() {
		var self = this;
		QueueCenter.getUserInfo().subscribe({
			onSuccess(result){
				self.setState({
					userInfo: result.data
				});
			},
			onError(result){
				location.href = "#/login"
			}
		}).fetch();

		QueueCenter.corpList().subscribe({
			onSuccess(result){
				let hospitalList = result.data.corpList.map((item,key)=>{
					return ({text:item.corpName,value:item.corpId})
				})
				self.setState({
					hospitalList: hospitalList
				})
				if(hospitalList.length == 1 || self.corpId == null) {
					self.corpId = (hospitalList[0] || {}).value;
					self.corpName = (hospitalList[0] || {}).text;
				}
				self.queryCorpArea()
			},
			onError(result){
				// location.href="/#/login"
			}
		}).fetch();
		if(this.corpId) {
			this.queryCorpArea()
		}
	}

	queryCorpArea(corpId){
		var self = this;
		var defaultArea = self.area;
		var areaItem = null;
		if(corpId || this.corpId) {
			QueueCenter.queryMyCorpArea(corpId || this.corpId).subscribe({
				onSuccess(result){
					let areaList = (result.data || []).map((item)=> {
						//上次选中的诊区本次设置为默认选中
						if(item.area == defaultArea){
							areaItem = {
								text: item.areaName,
								value: item.area,
								corpAreaType:item.corpAreaType,//0 门诊  1，急诊  2 医技
								isNurseCall:!!item.isNurseCall//该诊区是否需要 护士呼叫功能
							};
						}

						return ({
							text: item.areaName,
							value: item.area,
							corpAreaType:item.corpAreaType,
							isNurseCall:!!item.isNurseCall//该诊区是否需要 护士呼叫功能
						})
					});

					self.setState({
						areaList: areaList,
					});
					//如果没有默认选中
					if(!areaItem){
						areaItem = areaList[0] || {
							text:"未设置诊区",
							value:"",
							isNurseCall:false
						};
					}

					self.onAreaChange(areaItem);
					// self.props.onCorpAndAreaChange(self.corpId,self.corpName,areaItem.value,areaItem.text, !!areaItem.isNurseCall);
				},
				onError(result){

				}
			}).fetch()
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
	logout(){
		QueueCenter.logout().subscribe({
			onComplete:()=>{
				location.href="#/login"
			}
		}).fetch()
	}
	onCorpChange(corpName,corpId){
		this.corpId = corpId;
		this.corpName = corpName;
		this.queryCorpArea(corpId);
		localStorage.setItem("defaultCorpId", this.corpId);
		localStorage.setItem("defaultCorpName", this.corpName);
		this.setState({
			corpId:this.corpId,
			area:null
		})

	}

	onAreaChange(item, e){
		let {value, text, isNurseCall, corpAreaType} = item;

		this.props.onCorpAndAreaChange(this.corpId,this.corpName,value,text,isNurseCall, corpAreaType);
		localStorage.setItem("defaultCorpId", this.corpId);
		localStorage.setItem("defaultCorpName", this.corpName);
		localStorage.setItem("defaultArea", value);
		localStorage.setItem("defaultAreaName", text);

		this.setState({
			corpId:this.corpId,
			area:value
		})

	}
	render(){
		let {hospitalList,areaList,hover,userInfo, corpId, area} = this.state;
		return(
			<div className="page-nav" data-spm="header">
				{hospitalList.length > 1 ?
					<Dropdown list={hospitalList} title={"护士站"} defaultValue={this.corpName} onChange={(item)=> {
						this.onCorpChange(item.text, item.value)
					}}/>
					: <div className="dropdown">
					<h1>{(hospitalList[0]||{}).text || "护士站"}</h1>
				</div>
				}
				<HeaderText corpId={corpId} area={area}/>
				<div className="nav-right">
					<Dropdown list={areaList} title={"未配置区域"} defaultValue={this.areaName} onChange={this.onAreaChange.bind(this)}/>
					<div className={hover?"dropdown dropdown-action":"dropdown"} onMouseOver={this.onMouseOver.bind(this)}
					     onMouseLeave={this.onMouseLeave.bind(this)}
					>
						<div className="user">
							<div className="avatar" style={{backgroundImage:`url(${logo})`}}></div>
							<div className="name">{userInfo.name}</div>
						</div>
						<div className="dropdown-item-wrapper">
							<ul>
								<li className="dropdown-item" onClick={this.logout.bind(this)}>退出</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		)
	}
}
