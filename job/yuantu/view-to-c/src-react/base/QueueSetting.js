


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button,Menu, Table, Badge, Input, Popconfirm,Modal,Select, message} from 'react-piros'

import QueueCenter from '../../module/QueueCenter';


export default class QueueSetting extends React.Component{
  constructor(props) {

    super(props);

    this.state = {
      modal:null
    }

  }

  onSettingQueue(){
    this.setState({
      modal:{
        title:"队列设置",
        display:true,
        onOk:this.update.bind(this),
        onCancel:()=>{
          this.setState({
            modal:null
          })
        }
      }
    })
  }
  update(){

    let setting = this.refs["from"].getValue();
    let {corpId,area,queueId,closed,backType,doctorSel,preCount,queueLimitNum,doctorLimitNum} = setting;
    QueueCenter.updateQueueSetting(corpId,area,queueId,closed,backType,doctorSel,preCount,queueLimitNum,doctorLimitNum).subscribe({
      onSuccess:()=>{
        message.success("更新成功");
        this.setState({
          modal:null
        })
      },
      onError:()=>{
        message.error("更新失败，请稍后重试");
      }
    }).fetch();

  }
  renderModal(){
    let {modal} = this.state;
    if(modal){
      let {title, display, onOk, onCancel} = modal;
      let {corpId, area, queueCode} = this.props;
      return (
         <Modal title={title} display={display} onOk={onOk} onCancel={onCancel}>
          <QueueSet corpId={corpId} area={area} queueCode={queueCode} ref="from" />
         </Modal>
      );
    }
  }

  render(){

    return (
        <div style={{display:"inline-block", "lineHeight":"1.5em"}}>
          <a className="queue-setting" onClick={this.onSettingQueue.bind(this)} data-spm="setting">队列设置</a>
          {
            this.renderModal()
          }
        </div>
    )
  }

}


class QueueSet extends React.Component{
  constructor(props){
    super(props);

    this.queueStatus = [
      {
        text:"正常",
        value:0
      },
      {
        text:"关闭",
        value:1
      }
    ];

    this.backTypes = [
      {
        text:"交叉处理（推荐）",
        value:2
      },
      {
        text:"不处理",
        value:0
      },
      {
        text:"优先处理",
        value:1
      },
    ];


    this.isSelectDoctor = [
      {
        text:"是",
        value:1
      },
      {
        text:"否",
        value:0
      }
    ];

    this.queueNumber = [
      {
        text:"无预就诊",
        value:0
      },
      {
        text:"1",
        value:1
      },
      {
        text:"2",
        value:2
      },
      {
        text:"3",
        value:3
      },
      {
        text:"4",
        value:4
      },
      {
        text:"5",
        value:5
      }
    ];

    this.queueModel = [
      {
        text:"一次分诊",
        value:1
      },
      {
        text:"二次分诊",
        value:2
      }
    ];


    this.state = {
      id:null,
      queueName:"",
      closed:"",
      backType:"",
      doctoySel:"",
      preCount:"",
      queueLimitNum:"",
      doctorLimitNum:"",
      serviceMode:"",
      deptName:""
    }
  }

  componentDidMount() {
    let {area, corpId, queueCode} = this.props;
    QueueCenter.getQueryQueueInfo(corpId, area, queueCode).subscribe({
      onSuccess:(result)=>{
        let data = result.data;
        this.setState({
          id:data.id,
          queueName:data.queueName,//"",
          closed:data.closed,//1,
          backType:data.backType,//1,
          doctorSel:data.doctorSel,//1,
          preCount:data.preCount,//10,
          queueLimitNum:data.queueLimitNum,//10,
          doctorLimitNum:data.doctorLimitNum,//10,
          // serviceMode:data.serviceMode,//2
          deptName:data.smallDeptName || data.bigDeptName,//""
        })
      },
      onError:()=>{
        console.log("error")
      }
    }).fetch()
  }

  getValue(){
    let {corpId, area} = this.props;
    return {
      corpId:corpId,
      area:area,
      queueId:this.state.id,
      closed:this.refs['closed'].value,
      backType:this.refs['backType'].value,
      doctorSel:this.refs['doctorSel'].value,
      preCount:this.refs['preCount'].value,
      // serviceMode:this.refs['serviceMode'].value,
      queueLimitNum:this.refs['queueLimitNum'].value,
      doctorLimitNum:this.refs['doctorLimitNum'].value
    }
  }

  onChange(key){
    let value = this.refs[key].value;
    let state = this.state;
    state[key] = value;
    this.setState(state)
  }
  render(){

    let {
      queueName,
      closed,
      backType,
      doctorSel,
      preCount,
      queueLimitNum,
      doctorLimitNum,
      // serviceMode,
      deptName,
    } = this.state;
    return (
      <div className="queue-setting-from">
        <div className="item"><span>队列名：</span>{queueName}</div>
        <div className="item"><span>队列状态：</span><Select dataSource={this.queueStatus} ref="closed" onChange={this.onChange.bind(this, "closed")} value={closed} /></div>
        <div className="item"><span>回诊方式：</span><Select dataSource={this.backTypes} ref="backType" onChange={this.onChange.bind(this, "backType")} value={backType}  /></div>
        <div className="item"><span>是否可选医生：</span><Select dataSource={this.isSelectDoctor} ref="doctorSel" onChange={this.onChange.bind(this, "doctorSel")} value={doctorSel} /></div>
        <div className="item"><span>预就诊人数：</span><Select dataSource={this.queueNumber} ref="preCount" onChange={this.onChange.bind(this, "preCount")} value={preCount} /></div>
        <div className="item"><span>队列报警上限：</span><Input value={queueLimitNum} onChange={this.onChange.bind(this, "queueLimitNum")}  ref="queueLimitNum" /></div>
        <div className="item"><span>医生报警上限：</span><Input value={doctorLimitNum} onChange={this.onChange.bind(this, "doctorLimitNum")}ref="doctorLimitNum" /></div>
        <div className="item"><span>所在科室：</span>{deptName}</div>
      </div>
    )
  }
}
