import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select,Menu} from 'react-piros'


export default class DoctorCard extends React.Component{
	constructor(props) {
		super(props);
	}
	render(){
		return(
			<div className="doctor">
				<div className="doctor-header">
					<div className="doctor-photo"></div>
					<div className="doctor-name">周海军</div>
					<div className="doctor-level">
						<span className="level-item">副主任医师</span>
						<span className="level-item">专家</span>
					</div>
				</div>
				<div className="doctor-content">
					<div className="content-item">
						呼吸内科
					</div>
					<div className="content-item">
						呼吸内科
					</div>
					<div className="content-item">
						呼吸内科
					</div>
					<div className="content-item">
						呼吸内科
					</div>
					<div className="content-item">
						呼吸内科
					</div>
					<div className="content-item">
						...
					</div>
				</div>
				<div className="doctor-footer">
					<div className="footer-item">
						<div className="text">00097</div>
						<div className="text">工号</div>
					</div>
					<div className="footer-item">
						<div className="text">313</div>
						<div className="text">诊室</div>
					</div>
					<div className="footer-item">
						<div className="text">21</div>
						<div className="text">候诊人数</div>
					</div>
				</div>
			</div>
		)
	}
}