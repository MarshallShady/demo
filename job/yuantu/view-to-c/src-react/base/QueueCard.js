


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button,Menu, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'

import QueueCenter from '../../module/QueueCenter';


export default class QueueCard extends React.Component{
  constructor(props) {

    super(props);

    this.state = {
      allNum:"", //就诊总人数
      waitNum:"",//候诊中人数
      passNum:"", //过号人数
      completeNum:"", //已就诊人数
      backNum:"", //回诊人数
    };

    // {
    //     value:"1",
    //     text:"候诊中"
    //   },
    //   {
    //     value:"15",
    //     text:"所有就诊人"
    //   },
    //   {
    //     value:"3",
    //     text:"已就诊"
    //   },
    //   {
    //     value:"2",
    //     text:"已过号"
    //   },
    //   {
    //     value:"6",
    //     text:"回诊"
    //   }

  }

  componentDidMount() {
    let {corpId, queueCode, area} = this.props;
    this.loadDoctorInfo(corpId, queueCode, area);
  }
  componentWillReceiveProps(nextProps) {
    let {corpId, queueCode, area, doctorCode} = nextProps;
    let props = this.props;
    //优化性能
    if(
      corpId != props.corpId ||
      queueCode != props.queueCode ||
      area != props.area ||
      doctorCode != props.doctorCode
    ){
      this.loadDoctorInfo(corpId, queueCode, area, doctorCode);
    }
  }
  componentWillUnmount() {
    this.timer && this.timer.stop();
  }
  loadDoctorInfo(corpId, queueCode, area, doctorNo){
    this.timer && this.timer.stop()
    this.timer = QueueCenter.getAllPatientsByQueue(corpId, queueCode, area, doctorNo ).subscribe({
      onSuccess:(result)=>{
        // console.log( result );
        // this.reloaSideMenu(result.data)
        let data = result.data;

        this.setState({
          allNum:data.allNum,
          waitNum:data.waitNum,
          passNum:data.passNum,
          completeNum:data.completeNum,
          backNum:data.backNum
        })

      },
      onError(result){
        console.log(result.msg);
      }
    }).turns(10000);
  }

  onSelectCard(activeKey){
    console.log("queueCard onSelectCard")
    this.props.onSelectCard && this.props.onSelectCard(activeKey);
  }

  render(){

    let {
      allNum,
      waitNum,
      passNum,
      completeNum,
      backNum,

    } = this.state;
    let {activeKey} = this.props;

    return (
      <div className="triage-count" data-spm="queue-card">
        <div className={activeKey ==1 ? "item active" : "item"} data-spm="1" onClick={this.onSelectCard.bind(this, 1)}>
          <div className="icon ing"></div>
          <div className="number">{waitNum}</div>
          <div className="text">侯诊中人数</div>
        </div>
        <div  className={activeKey ==15 ? "item active" : "item"} data-spm="2" onClick={this.onSelectCard.bind(this, 15)} >
          <div className="icon count"></div>
          <div className="number">{allNum}</div>
          <div className="text">总就诊人数</div>
        </div>
        <div  className={activeKey ==3 ? "item active" : "item"} data-spm="3" onClick={this.onSelectCard.bind(this, 3)} >
          <div className="icon end"></div>
          <div className="number">{completeNum}</div>
          <div className="text">已就诊人数</div>
        </div>
        <div  className={activeKey ==2 ? "item active" : "item"} data-spm="4" onClick={this.onSelectCard.bind(this, 2)} >
          <div className="icon over"></div>
          <div className="number">{passNum}</div>
          <div className="text">过号人数</div>
        </div>
        <div  className="item no-hover"  >
          <div className="icon loop"></div>
          <div className="number">{backNum}</div>
          <div className="text">回诊人数</div>
        </div>
      </div>
    )
  }

}
