

import React from 'react'
import {Badge} from 'react-piros'

let statusText = {
    "0":'就诊中',
    "1":'候诊中',
    "2":'过号',
    "3":'就诊完成',
    "4":'预就诊',
    "5":'预就诊',
    "6":'回诊',
    "7":'弃号',
    "8":'退号',
    "9":"未签到",
    "10":"已过期"
}

export default class StatusTag extends React.Component{

  render(){
    let patient = this.props.patient;
    let status = patient.status;
    if(patient.status == 4 || patient.status == 5){ // 预就诊 绿色
      return <Badge type="weaker">{statusText[status]}</Badge>;
    }else if(patient.status == 0){//就诊中 红色
      return <Badge>{statusText[status] || status}</Badge>;
    }else{//其他状态 默认颜色
      return <Badge type="muted">{statusText[status] || status}</Badge>;
    }
  }
}
