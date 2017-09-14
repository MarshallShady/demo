


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select,message} from 'react-piros'
import QueueCard from './QueueCard'
import QueueSetting from './QueueSetting'
import QueueCenter from '../../module/QueueCenter'
import StatusTag from './StatusTag'
import utils from '../../libs/utils'


let ORDER_TYPE = {
  //系统过号患者标签
  NORMAL_PASS : "NORMAL_PASS",
  //待就诊
  PRE_IN : "PRE_IN",
  //回诊患者标签
  NORMAL_BACK : "NORMAL_BACK",
  //vip患者标签
  VIP : "VIP",
  //正常患者标签
  NORMAL : "NORMAL",
  //预约收费患者标签
  APPOINT_CHARGE : "APPOINT_CHARGE"
}

//corpAreaType == 1 急诊 需要提醒
export default class QueuePatientList extends React.Component{
  constructor(props) {
    super(props);

    //corpId, queueCode, area, queueName patientId
    //patientId 用于刷卡高亮处理患者
    //corpAreaType 队列类型 0门诊  1急诊 2医技
    let {corpId, area, queueCode, queueName, patientId, isNurseCall, corpAreaType} = this.props;

    this.corpId = corpId;
    this.area = area;

    //ajax请求次数统计，第一次请求用于定位刷卡患者位置
    this.ajaxLoadCount = 0;


    this.state = {
      filter:null,
      modalLoading:false,
      filterType:1,
      queueName:queueName,
      modal:null, //弹窗
      dataSource:[],//数据
      doctors:[],
      patientId:patientId,
      loading:false,
    }

    this.statusText = {
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
    };

    this.filterTypes = [
      {
        value:"1",
        text:"候诊中"
      },
      {
        value:"15",
        text:"所有就诊人"
      },
      {
        value:"3",
        text:"已就诊"
      },
      {
        value:"2",
        text:"已过号"
      }
    ];

    /**
      patientId:item.patientId,
      orderNo:item.orderNo,
      username:iten.username,
      status:item.status,
      doctorName:item.doctorName,
      key:item.patientId
    */
    let intervalFlag = {
      "1":"上午",
      "2":"下午"
    };

    let columnsMap = this.columnsMap = {
      "op":{
        title:"操作",
        key:"op",
        render:(text, item, index)=>{
          //1 可置顶 3，4可签到 2可召回
          /**
          {
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
          */
          let status = item.status;
          let isQiandao = status == 3 || status == 4 || status == 9;
          let isSetTop = status == 1;
          let isZhaohui = status == 2;
          let isChonghu = status == 4 || status == 5;
          let isNurseCallStatus = status == 1;
          return (
            <div className="table-opts" style={{display:"inline-block"}} onClick={(e)=>{ e.stopPropagation() }} >
              <a onClick={this.onViewUser.bind(this, item)} data-spm={"view"+index} >查看</a>
              {
                isQiandao ?  <a onClick={this.onQiandao.bind(this, item.patientId, item)} data-spm={"qiandao"+index}>签到</a> : <span data-spm={"qiandao"+index} >签到</span>
              }
              {
                isSetTop ?  <a onClick={this.onSetToTop.bind(this, item.patientId, item)} data-spm={"top"+index} >置顶</a> : <span data-spm={"top"+index}>置顶</span>
              }
              {
                isZhaohui ?  <a onClick={this.onSetBack.bind(this, item.patientId, item)} data-spm={"back"+index} >召回</a> : <span  data-spm={"back"+index} >召回</span>
              }
              {
                isChonghu ? <a  onClick={this.onReCall.bind(this, item.patientId, item)} data-spm={"recall"+index} >重呼</a> : null
              }
              {
                //急诊
                this.renderAreaType(item, corpAreaType, isNurseCall && isNurseCallStatus)
              }
            </div>
          )
        }
      },
      "time":{
        title:"就诊时间",
        key:"time"
      },
      "endTime":{
        title:"完成时间",
        key:"endTime"
      },
      "orderNoTag":{
        title:"序号",
        key:"orderNoTag",
        render:(text, item)=>{
          return <span>
            {intervalFlag[item.intervalFlag] +" "+ text + "号"}
            {item.regMode == 1 ? <Badge type="weaker">预约</Badge> : null}
          </span>;
        }
      },
      "status":{
        title:"就诊状态",
        key:"status",
        render:(text, item)=>{
          return <StatusTag patient={item} />
        }
      },
      "username":{
        title:"姓名",
        key:"username",
        render:(text, item)=>{
          //优先显示回诊标签
          return <div>
            {text}
            {
                item.isBack == 1 ?
                  <Badge type="weaker">回诊</Badge> :
                  item.orderType == ORDER_TYPE.NORMAL_PASS ? <Badge>过号</Badge> : null
            }
          </div>
        }
      },
      "doctorName":{
        title:"医生姓名",
        key:"doctorName",
        render:(text, item)=>{
          if(item.queueType == 0 && item.doctors.length){
            return <div style={{width:100}} onClick={(e)=>{ e.stopPropagation() }} >
              <Select
                dataSource={item.doctors}
                value={item.doctorNo}
                placeholder="选择医生"
                onChange={(doctorNo, selectItem)=>{
                  this.onSelectDoctor(doctorNo, selectItem.text, item.patientId, item.username)
                }}
              />
            </div>
          }else{
            return text;
          }
        }
      },
      "level":{
        title:"等级",
        key:"medLevel",
        render:(text, item)=>{
          //陆涛说默认是4级
          let level = {"4":"Ⅳ", "3":"Ⅲ"}[text];
          return level ? <Badge>{level}</Badge> : null
        }
      }
    }

    this.columns = [
      columnsMap['op'],
      columnsMap['time'],
      columnsMap['endTime'],
      columnsMap['orderNoTag'],
      columnsMap['status'],
      columnsMap['username'],
      columnsMap['doctorName']
    ];

    //急诊
    if(corpAreaType == 1){
      this.columns = [
        columnsMap['op'],
        columnsMap['time'],
        columnsMap['endTime'],
        columnsMap['level'],
        columnsMap['status'],
        columnsMap['username'],
        columnsMap['doctorName']
      ];
    }

    this.selectedRows = null;

    this.rowSelection =  {
        type:"checkbox",
        selectedRowKeys:[parseInt(patientId)],
        onChange:(selectedRowKeys, selectedRows)=>{
          this.selectedRows = selectedRows;
        }
    }
  }
  //选择特殊的诊区操作
  renderAreaType(item, corpAreaType, isNurseCall){

    if(isNurseCall){
      //如果上次没有呼叫过，就用挂号时间作为等候起始时间
      let waitingTime = (Date.now() - new Date(item.preNurseCallTime || item.regTime)) / 1000 / 60;
      let isShowTips = false;//corpAreaType == 1 //Ⅲ Ⅳ
      if(corpAreaType == 1){
        //急诊病人
        if(item.medLevel == "3" && waitingTime - item.threeLevelTime > 0){
          isShowTips = true;
        }
        if(item.medLevel == "4" && waitingTime - item.fourLevelTime > 0){
          isShowTips = true;
        }
      }
      return <a onClick={this.onYujianCall.bind(this, item.patientId, item)} data-spm={"yujianCallji"} >
        预检呼叫{item.nurseCallTimes > 0 ? `(${item.nurseCallTimes}次)` : null}
        {waitingTime > 0 ? <Badge type={isShowTips?"":"muted"}>已等候{parseInt(waitingTime)}分钟</Badge> : null}
      </a>
    }

    return null;
  }

  componentDidMount() {
    this.reloadPageData( this.props );
  }

  componentWillReceiveProps(nextProps) {
    let {corpId, area, queueCode, queueName, doctorCode, activeKey, patientId} = this.props;

    //如果在当前页面刷卡，需要选中设置选中key
    if(nextProps.patientId != patientId){
      patientId = Number(nextProps.patientId);
      this.rowSelection.selectedRowKeys = [patientId];
    }

    if(
      nextProps.corpId != corpId ||
      nextProps.area != area ||
      nextProps.queueCode != queueCode ||
      nextProps.doctorCode != doctorCode ||
      nextProps.activeKey != activeKey ||
      nextProps.patientId != patientId
    ){
      //重置ajax请求次数
      this.ajaxLoadCount = 0;
      this.setState({
        loading:true,
        patientId:patientId,
        dataSource:[]
      });

      this.reloadPageData( nextProps )
    }
  }
  componentWillUnmount(){
    this.queuePatientListTimer && this.queuePatientListTimer.stop();
  }
  //重新获取页面数据
  reloadPageData(props){
    props = props || this.props;
    let {corpId, area, queueCode, queueName, doctorCode, activeKey} = props;

    this.setState({
      queueName:queueName
    });
    this.queryQueuePainterList(corpId, queueCode, area, doctorCode, activeKey);
  }
  //预检呼叫，用户把就诊人呼叫到护士台
  onYujianCall(patientId, item){
    let {area, corpId} = this.props;
    let username = item.username;
    this.setState({
      modal:{
        title:"把患者呼叫到护士台",
        display:true,
        cancelButton:true,
        onOk:()=>{

          QueueCenter.yujianCall(patientId, corpId).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{

              message.success("呼叫成功");
              this.onDialogCancel();

            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:(<div>在大屏中呼叫患者<Badge type="muted">{username}</Badge>到护士台。</div>)
      }
    });
  }
  onReCall(patientId, item){
    let {area} = this.props;
    let username = item.username;
    this.setState({
      modal:{
        title:"在大屏上再次语音呼叫患者",
        display:true,
        cancelButton:true,
        onOk:()=>{

          QueueCenter.recall(patientId).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{

              message.success("重呼成功");
              this.onDialogCancel();

            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:(<div>确认在大屏中再次语音呼叫<Badge type="muted">{username}</Badge>患者</div>)
      }
    });
  }
  //选择医生
  onSelectDoctor(doctorNo, doctorName, patientId, username){
    let {area} = this.props;
    this.setState({
      modal:{
        title:"帮助患者选择就诊医生",
        display:true,
        cancelButton:true,
        onOk:()=>{

          QueueCenter.selectDoctors(patientId, doctorNo, area).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{

              message.success("选择医生成功");
              this.reloadPageData();
              this.onDialogCancel();

            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:(<div>确认为患者 <Badge type="muted">{username}</Badge> 选择 <Badge type="muted">{doctorName}</Badge> 医生看诊</div>)
      }
    });

  }
  //召回
  onSetBack(patientId, item){

    let {corpId, area} = this.props;
    let username = item.username;
    this.setState({
      modal:{
        title:"召回患者",
        display:true,
        cancelButton:true,
        onOk:()=>{

          QueueCenter.zhaohui(patientId, corpId, area).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{

              message.success("召回患者成功");
              this.reloadPageData();
              this.onDialogCancel();

            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:(<div>召回 <Badge type="weaker">{username}</Badge> 患者</div>)
      }
    });
  }
  //签到
  onQiandao(patientId,item,e){

    let {corpId, area} = this.props;
    let defaultValue = item.doctorNo ? item.doctorNo : "";
    let doctors = item.doctors;
    let doctorsCount = item.doctors.length;
    if(doctorsCount == 1){
      //只有一个医生的时候就默认选中这个医生
      defaultValue = item.doctors[0].value;
    }else{
      //有两个医生，或者没有医生的情况 添加一个自动分配
      doctors = [
        {
          text:"自动分配医生",
          value:""
        }
      ].concat(item.doctors);
    }
    //判断 defaultValue 的值 是否在 doctors 中，避免显示为空，其实可以在select组件中添加这个逻辑
    let defaultValueIsInValues = false;
    doctors.map((item)=>{
      if(item.value === defaultValue){
        defaultValueIsInValues = true;
      }
    });

    if(!defaultValueIsInValues){
      defaultValue = "";
    }


    this.setState({
      modal:{
        title:"签到确认",
        display:true,
        cancelButton:true,
        onOk:()=>{

          let doctorNo = this.refs["qiandaoDoctorNo"].value;//qiandaoDoctorNo
          QueueCenter.setQiandao(patientId, corpId, area, doctorNo).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{

              message.success("签到成功");
              this.reloadPageData();
              this.onDialogCancel();


            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:function(){
          return <div>
            为 <Badge type="weaker">{item.username}</Badge>签到？
            <div style={{width:150, float:"right", marginTop:-15, marginRight:20}}>
              <Select dataSource={doctors}  defaultValue={defaultValue} ref="qiandaoDoctorNo" style={{width:150}} ></Select>
            </div>
          </div>
        }
      }
    })
  }

  onSetToTop(patientId, item){
    //setToQueueTop
    let {corpId, area} = this.props;
    this.setState({
      modal:{
        title:"置顶确认",
        display:true,
        cancelButton:true,
        onOk:()=>{

          QueueCenter.setToQueueTop(patientId, corpId, area).subscribe({

            onSuccess:()=>{
              message.success("置顶成功");
              this.reloadPageData();
              this.onDialogCancel();
            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();

        },
        onCancel:this.onDialogCancel.bind(this),
        context:(<div>确认将患者 <Badge type="weaker">{item.username}</Badge> 在候诊队列置顶</div>)
      }
    });


  }
  //弃号
  onSetAbandonNo(){

    let selectedRows = this.selectedRows;
    if(!(selectedRows && selectedRows.length)){
      message.error("请选中要操作的患者");
      return ;
    }
    let names = selectedRows.map((item)=>{
      return item.username;
    });

    let ids = selectedRows.map((item)=>{
      return item.key;
    });

    this.setState({
      modal:{
        title:"置为已就诊确认",
        display:true,
        cancelButton:true,
        onOk:()=>{

          QueueCenter.abandonNo(ids.join()).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{
              message.success("操作成功");
              this.reloadPageData();
              this.onDialogCancel();
            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:(<div>确认把
            {
              names.map((name, index)=>{
                if(index > 10){
                  return null
                }
                return <Badge type="weaker" key={index}>{name}</Badge>
              })
            }
            {
              names.length > 10 ?  <Badge>等{names.length-10}位</Badge> : null
            }
            患者设置为已就诊状态
        </div>)
      }
    })
  }
  //批量选医生
  onSelectDoctorAll(){
    let selectedRows = this.selectedRows;
    if(!(selectedRows && selectedRows.length)){
      message.error("请选中要操作的患者");
      return ;
    }
    let names = selectedRows.map((item)=>{
      return item.username;
    });

    let ids = selectedRows.map((item)=>{
      return item.key;
    });

    // let {corpId, area} = this.props;
    let defaultValue = "";
    let doctors = this.state.doctors;
    let doctorsCount = doctors.length;
    if(doctorsCount == 1){
      //只有一个医生的时候就默认选中这个医生
      defaultValue = doctors[0].value;
    }else{
      //有两个医生，或者没有医生的情况 添加一个自动分配
      doctors = [
        {
          text:"自动分配医生",
          value:""
        }
      ].concat(doctors);
    }

    this.setState({
      modal:{
        title:"为患者选择看诊医生",
        display:true,
        cancelButton:true,
        onOk:()=>{

          let doctorNo = this.refs["piliangQiandaoDoctorNo"].value;//qiandaoDoctorNo
          QueueCenter.batchSelectDoctor(ids.join(), doctorNo).subscribe({
            onSendBefore:()=>{
              this.setState({
                modalLoading:true
              })
            },
            onComplete:()=>{
              this.setState({
                modalLoading:false
              })
            },
            onSuccess:()=>{
              message.success("操作成功");
              this.reloadPageData();
              this.onDialogCancel();
            },
            onError:(result)=>{
              message.error(result.msg)
            }
          }).fetch();


        },
        onCancel:this.onDialogCancel.bind(this),
        context:function(){
          return (
            <div style={{lineHeight:"3em"}}>为患者
              {
                names.map((name, index)=>{
                  if(index > 7){
                    return null
                  }
                  return <Badge type="weaker" key={index}>{name}</Badge>
                })
              }
              {
                names.length > 7 ?  <Badge>等{names.length-7}位</Badge> : null
              }
              选择医生看诊
              <div style={{width:150, float:"right", marginTop:-7, marginRight:20}}>
                <Select dataSource={doctors}  defaultValue={defaultValue} ref="piliangQiandaoDoctorNo" style={{width:150}} ></Select>
              </div>
            </div>
          )
        }
      }
    })
  }

  //
  onViewUser(item){
    /**
      性别 ：女
      年龄 ：
      门诊号 ：4363515
      联系电话 ：无
      科室 ：杨敏
      诊室 ：
      就诊医生 ：杨敏
      就诊状态 ：候诊中
    **/
    let sexs = {
      "1":"男",
      "2":"女"
    };

    this.setState({
      modal:{
        title:"用户信息",
        display:true,
        cancelButton:false,
        onOk:this.onDialogCancel.bind(this),
        onCancel:this.onDialogCancel.bind(this),
        context:(
          <div className="view-user-info">
            <div><span>姓名 ：</span>{item.username}</div>
            {item.sex ? <div><span>性别 ：</span>{sexs[item.sex]}</div> : null}
            {item.age ? <div><span>年龄 ：</span>{item.age}</div> : null}
            <div><span>门诊号 ：</span>{item.patientNo}</div>
            <div><span>联系电话 ：</span>{item.mobile}</div>
            <div><span>科室 ：</span>{item.smallDeptName || item.bigDeptName}</div>
            <div><span>诊室 ：</span>{item.diagRoom}</div>
            <div><span>就诊医生 ：</span>{item.doctorName}</div>
            <div><span>就诊状态 ：</span>{this.statusText[item.status]}</div>
            <div><span>挂号时间 ：</span>{item.regTime}</div>
            <div><span>签到时间 ：</span>{item.waitingTime}</div>
            <div><span>大屏叫号时间 ：</span>{item.preTime}</div>
          </div>
        )
      }
    })
  }

  onDialogCancel(){
    this.setState({
      modal:null
    })
  }

  queryQueuePainterList(corpId, queueCode, area, doctorCode, filter){

    filter = filter || 1;
    this.queuePatientListTimer && this.queuePatientListTimer.stop();
    this.queuePatientListTimer = QueueCenter.getQueuePatientList(corpId, queueCode, area,doctorCode, filter).subscribe({
      onSendBefore:()=>{
        // message.loading("加载中...");
        this.setState({
          loading:true
        })
      },
      onComplete:()=>{
        // message.hide();
        this.setState({
          loading:false
        })
      },
      onSuccess:(result)=>{
        //如果异步回来的数据不等于筛选条件就丢弃
        if(filter != this.props.activeKey){
          return ;
        }
        let allPatientList = result.data.allPatientList;
        let threeLevelTime = result.data.threeLevelTime;
        let fourLevelTime = result.data.fourLevelTime;

        let doctors = result.data.doctors.map((item)=>{
          return {
            text:item.doctorName+(item.diagRoom?`(${item.diagRoom})`:""),
            value:item.doctorNo
          }
        });
        let queueType = result.data.queueType;

        let dataSource = allPatientList.map((item)=>{
          return {

            patientId:item.id,
            patientNo:item.patientNo,
            orderNo:item.orderNo,
            orderNoTag:item.orderNoTag,
            username:item.username,
            status:item.status,
            doctorNo:item.doctorNo,
            doctorName:item.doctorName,
            key:item.id,
            sex:item.sex,
            age:item.age,
            mobile:item.mobile,
            smallDeptName:item.smallDeptName,
            bigDeptName:item.bigDeptName,
            diagRoom:item.diagRoom,
            doctors:doctors,
            queueType:queueType,
            medLevel:item.medLevel,//急诊等级
            threeLevelTime:threeLevelTime,//3级病人提醒时间
            fourLevelTime:fourLevelTime,//4级病人提醒时间
            preNurseCallTime:item.preNurseCallTime,//距离上次呼叫
            intervalFlag:item.intervalFlag,
            waitingTime:item.waitingTime,//签到时间
            preTime:item.preTime, //大屏呼叫时间
            callingTime:item.callingTime, //医生呼叫时间
            regTime:item.regTime,//挂号时间
            endTime:utils.dateFormat(item.completeTime, "MM-dd hh:mm"), //就诊完成时间
            time:utils.dateFormat(item.regMode == 1 ? item.startTime : item.regTime, "MM-dd hh:mm"),
            completeTime:item.completeTime,//完成就诊时间
            nurseCallTimes:item.nurseCallTimes, //预检呼叫次数
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
        });

        this.setState({
          queueName:result.data.queueName,
          queueType:queueType, //0可选医生 1不可选
          doctors:doctors,
          dataSource:dataSource
        });

        this.onDataReload(doctors, dataSource);
        //请求次数加1
        this.ajaxLoadCount++;
        this.locationPatient();
      },
      onError:(result)=>{
        this.setState({
          dataSource:[]
        })
      }
    }).turns(5000);

  }
  //第一次加载页面定位患者
  locationPatient(){
    setTimeout(()=>{
      //如果是第一次请求，执行定位患者位置逻辑
      if(this.ajaxLoadCount == 1 && this.state.patientId){
        let input = document.querySelector(".page-context table input:checked");
        if(input){
          let clientRect = input.getBoundingClientRect();
          window.scrollTo(0,clientRect.top-170);
        }
      }
    }, 100)
  }

  renderQiandaoConfirm(item){
    return <div className="table-popover">
      为 {item.username}  签到？
      <div className="opts">
        <Button size="sm" secondary={true}>取消</Button>
        <Button size="sm">确认</Button>
      </div>
    </div>
  }

  renderModal(){

    let {modal, modalLoading} = this.state;
    if(modal){

      let {title, display, onOk, onCancel, context, cancelButton} = modal;

      return (
         <Modal title={title} display={display} onOk={onOk} loading={modalLoading} cancelButton={cancelButton} onCancel={onCancel}>
          {typeof context == "function" ? context() : context}
         </Modal>
      );
    }

    return null
  }

  onFilterKeyChange(){
    let filter = this.refs["filter"].value;
    this.setState({
      filter:filter
    })
  }
  //数据刷新后回调
  onDataReload(doctors, dataSource){
    this.props.onDataReload && this.props.onDataReload(doctors, dataSource);
  }
  onChangeFilterType(value){

    if(value != this.props.activeKey){
      this.setState({
        dataSource:[]
      }, ()=>{
        // this.reloadPageData(this.props);
      });

      this.props.onSelectCard && this.props.onSelectCard(value);
    }

  }
  render(){

    let {dataSource, loading, filter, filterType, doctors, patientId} = this.state;
    let {queueName,activeKey} = this.props;
    doctors = [{"text":"全部", value:0}].concat(doctors);

    if(filter){
      dataSource = dataSource.filter((item)=>{
        return String(item.orderNo).indexOf(filter) != -1 || String(item.username).indexOf(filter) != -1;// == filter
      });
    }

    let columns = this.columns;
    //就诊中，已过号 不显示完成时间
    if(activeKey == "1" || activeKey == "2"){
      columns = columns.filter((item)=>{
        return item.key != "endTime";
      });
    }

    return (
      <div>
        <div style={{position:"relative"}}  >
          <span style={{marginRight:10}}>
            <Button onClick={this.onSelectDoctorAll.bind(this)} data-spm="queue-table-selectdoc" >选择医生</Button>
          </span>
          <Button onClick={this.onSetAbandonNo.bind(this)} data-spm="queue-table-opt" >置为已就诊</Button>
          <div style={{verticalAlign:"middle", display:"inline-block", marginLeft:30}}>
            <Select dataSource={this.filterTypes} value={activeKey} onChange={this.onChangeFilterType.bind(this)} />
          </div>
          <div style={{width:200, position:"absolute", right:10, top:15}}>
            <Input type="text"  placeholder="序号，患者姓名" ref="filter" onChange={this.onFilterKeyChange.bind(this)} style={{margin:0}} />
          </div>
        </div>
        <div style={{marginBottom:100}} data-spm="queue-table">
          <Table dataSource={dataSource} isIndex={true} columns={columns} loading={loading} rowSelection={this.rowSelection} ></Table>
        </div>
        {
          this.renderModal()
        }
      </div>
    )
  }

}
