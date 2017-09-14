
import React from 'react'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select,message} from 'react-piros'
import QueueCenter from '../../module/QueueCenter'
import StatusTag from './StatusTag'
import utils from '../../libs/utils'


// 急诊预检提醒
export default class JiZhenYuJianTips extends React.Component{
  constructor(props){
    super(props)
    this.state = {
      isShowList:false,
      list:[]
    }

    this.columns = [
      {
        title:"姓名",
        key:"username",
      },
      {
        title:"等级",
        key:"medLevel",
        render:(text,item)=>{
          //陆涛说默认是4级
          let level = {"4":"Ⅳ", "3":"Ⅲ"}[text];
          return <Badge>{level}</Badge>
        }
      },
      {
        title:"队列",
        key:"queueName"
      },
      {
        title:"状态",
        key:"status",
        render:(text, item)=>{
          return <StatusTag patient={item} />
        }
      },
      {
        title:"总等候时间",
        key:"regTime",
        render:(text, item)=>{
          let waitingTime = (Date.now() - new Date(item.regTime)) / 1000 / 60;
          return <Badge type="muted">总等候{parseInt(waitingTime)}分钟</Badge>
        }
      },
      {
        title:"距离上次呼叫已等候",
        key:"preNurseCallTime",
        render:(preNurseCallTime, item)=>{
          //如果上次没有呼叫过，就用挂号时间作为等候起始时间
          let waitingTime = (Date.now() - new Date(item.preNurseCallTime || item.regTime)) / 1000 / 60;
          // let isShowTips = false;//corpAreaType == 1 //Ⅲ Ⅳ
          //急诊病人
          // if(item.medLevel == "3"){
          //   isShowTips = true;
          // }
          // if(item.medLevel == "4" && waitingTime - item.fourLevelTime > 0){
          //   isShowTips = true;
          // }

          return waitingTime > 0 ?<Badge>已等候{parseInt(waitingTime)}分钟</Badge> : null;

        }
      },
      {
        title:"操作",
        key:"opt",
        render:(text, patient)=>{
          return <div>
            <a className="txt-link" onClick={this.yujianCall.bind(this, patient.patientId)}>
              预检呼叫 {patient.nurseCallTimes > 0 ? `(${patient.nurseCallTimes})`:null}
            </a> | <a
               className="txt-link"
              href={`#/queue?queueCode=${patient.queueCode}&queueName=${patient.queueName}&patientId=${patient.patientId}`}
              onClick={this.onCloseView.bind(this)}
              >在队列中查看</a>
          </div>
        }
      }
    ];
  }
  componentDidMount(){
    this.getJizhenYuJianList();
  }

  getJizhenYuJianList(){
    let {corpId, area} = this.props;
    QueueCenter.getJizhenYuJianList(corpId, area).subscribe({
      onSuccess:(result)=>{
        this.setState({
          list:result.data.map((item)=>{
            return {
              key:item.id,
              patientId:item.id,
              patientNo:item.patientNo,
              orderNo:item.orderNo,
              orderNoTag:item.orderNoTag,
              username:item.username,
              queueCode:item.queueCode,
              status:item.status,
              doctorNo:item.doctorNo,
              doctorName:item.doctorName,
              queueName:item.queueName,
              key:item.id,
              sex:item.sex,
              age:item.age,
              mobile:item.mobile,
              smallDeptName:item.smallDeptName,
              bigDeptName:item.bigDeptName,
              diagRoom:item.diagRoom,
              medLevel:item.medLevel,//急诊等级
              // threeLevelTime:threeLevelTime,//3级病人提醒时间
              // fourLevelTime:fourLevelTime,//4级病人提醒时间
              intervalFlag:item.intervalFlag,
              waitingTime:item.waitingTime,//签到时间
              preTime:item.preTime, //大屏呼叫时间
              callingTime:item.callingTime, //医生呼叫时间
              regTime:item.regTime,//挂号时间
              endTime:utils.dateFormat(item.completeTime, "MM-dd hh:mm"), //就诊完成时间
              time:utils.dateFormat(item.regMode == 1 ? item.startTime : item.regTime, "MM-dd hh:mm"),
              completeTime:item.completeTime,//完成就诊时间
              nurseCallTimes:item.nurseCallTimes, //预检呼叫次数
              preNurseCallTime:item.preNurseCallTime,//距离上次呼叫的时间
              /**
                //系统过号患者标签
                NORMAL_PASS = "NORMAL_PASS";
                //待就诊
                PRE_IN = "PRE_IN";
                //回诊患者标签
                NORMAL_BACK = "NORMAL_BACK";
                //vip患者标签
                VIP = "VIP";
                //正常患者标签
                NORMAL = "NORMAL";
                //预约收费患者标签
                APPOINT_CHARGE = "APPOINT_CHARGE";
              **/
              orderType:item.orderType,//
              isBack:item.isBack, //1回诊  0正常
              regMode:item.regMode,//1预约 2挂号

            }
          })
        })
      }
    }).turns(1000*10);
  }
  yujianCall(patientId){
    console.log(patientId)
    QueueCenter.yujianCall(patientId, this.props.corpId).subscribe({
      onSuccess:()=>{
        message.success("呼叫成功");
        this.getJizhenYuJianList();
      },
      onError:(result)=>{
        message.error(result.msg || "呼叫失败,请稍后再试");
      }
    }).fetch();
  }
  onViewPatient(){

    this.setState({
      isShowList:true
    })
  }
  onCloseView(){
    this.setState({
      isShowList:false
    })
  }

  render(){
    let {list, isShowList} = this.state;
    return <div>
        <div className="jizhen-global-tips" onClick={this.onViewPatient.bind(this)}>
          预检提醒
          {list.length > 0 ? <div>({list.length})</div> : null}
        </div>
        {
          isShowList ? <Modal style={{width:900}} display={true} title="需要预检的患者列表" onOk={this.onCloseView.bind(this)} onCancel={this.onCloseView.bind(this)} cancelButton={false} >
            <div style={{height:300, overflow:"auto"}}><Table dataSource={list}  columns={this.columns} /></div>
          </Modal> : null
        }
    </div>
  }
}
