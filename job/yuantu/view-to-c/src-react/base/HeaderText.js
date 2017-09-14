import React from 'react'
import {render} from 'react-dom'
import {Button,Modal,message} from 'react-piros'
import QueueCenter from '../../module/QueueCenter'
import LocalCenter from '../../module/LocalCenter'

import '../../css/base/header.less'


export default class HeaderText extends React.Component{
	constructor(props){
		super(props)
		this.state={
			display: false,
			head:"",
			textarea:""
		}
	}

	onClick(type){
		let head = "",
			textarea = "";
		if(type=='text'){
			head = "大屏底部提示语";
			textarea = localStorage.getItem("headerText") || "";
		}else if(type == 'voice'){
			head = "诊区语音通知"
			textarea = localStorage.getItem("headerVoice") || "";
		}
		this.setState({
			display:true,
			head,
			textarea,
			type
		})
	}

	onChange(){
		let {textarea} = this.state;
		textarea = this.refs.textarea.value;
		this.setState({
			textarea
		})
	}

	hide(){
		this.setState({
			display:false
		})
	}

	onOk(){
		let {type,textarea} = this.state;
		if(type == 'text'){
			localStorage.setItem("headerText",textarea)
			this.updateDeviceAdvertise(textarea)
		}else{
			localStorage.setItem("headerVoice",textarea)
			this.updateDeviceVoice(textarea)
		}
	}


	updateDeviceAdvertise(Advertise){
		QueueCenter.updateDeviceAdvertise(this.props.corpId,this.props.area,Advertise).subscribe({
			onSuccess(result){
				message.success("更新成功")
			},
			onError(error){
				message.error("更新失败")
			}
		}).fetch()
	}

	updateDeviceVoice(voice){
		QueueCenter.updateDeviceVoice(this.props.corpId,this.props.area,voice).subscribe({
			onSuccess(result){
				message.success("更新成功")
			},
			onError(error){
				message.error("更新失败")
			}
		}).fetch()
	}

	playVoice(){
		QueueCenter.playVoice(this.props.corpId,this.props.area,).subscribe({
			onSendBefore(){
				message.info("准备播放语音")
			},
			onSuccess(result){
				message.success("播放成功")
			},
			onError(error){
				message.error("播放失败")
			}
		}).fetch()
	}
	render(){
		let {display,head,textarea} = this.state;
		return(
			<div className="header-item">
				<span className="item"><span className="text">大屏底部提示语</span><span className="item-btn" onClick={this.onClick.bind(this,'text')} data-spm="advertise">编辑</span></span>
				<span className="item"><span className="text">诊区语音通知</span><span className="item-btn" onClick={this.onClick.bind(this,'voice')} data-spm="voice">编辑</span>
					<span className="play" onClick={this.playVoice.bind(this)} data-spm="voicePlay"></span>
				</span>
				<Modal title={head} display={display}
				       cancelButton={false}
				       onOk={()=>{
					       this.hide();
					       this.onOk();
				       }}
				       onCancel={this.hide.bind(this)}
				>
					<textarea ref="textarea" value={this.state.textarea} onChange={this.onChange.bind(this)} ></textarea>
					<span></span>
				</Modal>
			</div>
		)
	}
}
