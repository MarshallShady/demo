import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select,Menu,Tabs, Tooltip} from 'react-piros'
import QueueCenter from '../../module/QueueCenter'
import Loading from './Loading'
import util from '../../libs/utils'

let TabItem = Tabs.TabItem;
//护士台刷卡模块
export default class WaitUseCard extends React.Component{
	constructor(props) {
		super(props);

    this.state = {
      loadingInfo:false,
      loadingCard:true,
      display:false,
      showTips:false,
      cardRenderStatus:true,
      cardno:null,
      tips:"外接读卡器工作正常"
    }

    this.intervalFlag = {
      "1":"上午",
      "2":"下午"
    }
	}
  componentDidMount() {

    let getCardInfo = QueueCenter.getNativeCardInfo().subscribe({
      onSuccess:(result)=>{
        console.log(result)
        if(result.success){
          let data = result.data;
          this.setState({
            cardno:data.cardno
          })
          this.showPatientInfo(data.cardno, data.cardtype);
        }

        this.setState({
          tips:"外接读卡器工作正常"
        });
      },
      onError:()=>{
        this.setState({
          cardRenderStatus:false,
          tips:"读卡器异常"
        });
      }
    });
    // getCardInfo.mock({"success":true,"data":{"cardno":"16800766","cardtype":"2"},"msg":"m1卡读序列号成功"})
    getCardInfo.turns(2000);
  }

  showPatientInfo(cardNo,cardType){
    let corpId = this.props.corpId;
    // let corpId = 261; //todo
    console.log(123)
    QueueCenter.getPatientInfo(cardNo,cardType,corpId).subscribe({
      onSendBefore:()=>{
        this.setState({
          loadingInfo:true,
          display:true,
        })
      },
      onSuccess:(result)=>{
        console.log(result)
        this.setState({
          loadingInfo:false,
          display:true,
          userInfos:result.data
        })
      },
      onError:()=>{
        this.setState({
          loadingInfo:false,
          display:true,
          userInfos:null
        })
      }
    }).fetch();
    // .mock({
    //   success:true,
    //   data:[
    //     {
    //       // id: 55173750,
    //       // id:55173768,
    //       // id:55173805,
    //       orderNo: 8,
    //       orderType: null,
    //       weight: 800,
    //       username: "李文畅",
    //       sex: null,
    //       patientNo: null,
    //       doctorName: "崔鑫",
    //       diagRoom: "耳鼻喉科诊室1",
    //       diagRoomCode: null,
    //       callTimes: 1,
    //       status: 0,
    //       createTime: "2017-04-27 08:10:32",
    //       modifyTime: "2017-04-27 08:10:09",
    //       age: null,
    //       mobile: "13969840188",
    //       isBack: 0,
    //       corpId: "261",
    //       corpName: "",
    //       waitingTime: null,
    //       preTime: "2017-04-27 14:21:33",
    //       callingTime: "2017-04-27 15:32:54",
    //       completeTime: null,
    //       passTime: null,
    //       guardNo: null,
    //       smallDeptCode: "0010",
    //       smallDeptName: "耳鼻喉科",
    //       bigDeptCode: "",
    //       bigDeptName: "",
    //       cardNo: "37082919830313066X",
    //       cardType: 1,
    //       regId: "129459582",
    //       intervalFlag: 2,
    //       regTime: "2017-04-27 08:00:00",
    //       idNo: null,
    //       doctorNo: "001186",
    //       doctorCode: null,
    //       doctorChoose: 0,
    //       area: "A",
    //       queueCode: "emp001182",
    //       queueName: "耳鼻喉科(普通)",
    //       patientType: 0,
    //       orderNoTag: "08",
    //       backType: null,
    //       count: 0,
    //       diagRoomAlias: null,
    //       doctorSel: null,
    //       doctorAddress: "",
    //       regType: null,
    //       regMode: 2,
    //       patientList: null,
    //       currentPatient: null,
    //       doctLogo: "",
    //       realName: "王泽凯",
    //       queueType: null,
    //       deptMap: null,
    //       backFlag: false
    //     }
    //   ]
    // });
  }

  onCancel(){
    this.setState({
      display:false,
      loadingInfo:false,
    })
  }

  onViewInQueue(){
    let {userInfos} = this.state;
    let currentIndex = this.refs['patientTabs'].state.currentIndex;
    // console.log(userInfos)
    let userInfo = userInfos[currentIndex];
    let {queueCode, queueName, doctorCode, id} = userInfo;

    window.location.hash = "#/queue?"+util.flat({
      queueCode,
      queueName,
      doctorCode,
      patientId:id //用户ID
    });

    this.setState({
      display:false
    });


  }

  renderUserInfos(){

    let {cardno,userInfos,loadingInfo} = this.state;
    if(userInfos){
      return <Tabs defaultActiveKey="0" ref="patientTabs" >
          {
            userInfos && userInfos.map((item, index)=>{
              return (
                <TabItem tabNavItem ={item.queueName} key={index} >
                  <div className="view-user-info">
                    <div><span>姓名 ：</span>{item.username}</div>
                    <div><span>所在队列：</span>{item.queueName}</div>
                    <div><span>叫号信息：</span><Badge type="weaker">{this.intervalFlag[item.intervalFlag]+item.orderNoTag}</Badge></div>
                    {item.sex ? <div><span>性别 ：</span>{sexs[item.sex]}</div> : null}
                    {item.age ? <div><span>年龄 ：</span>{item.age}</div> : null}
                    {item.patientNo ? <div><span>门诊号 ：</span>{item.patientNo}</div> : null}
                    <div><span>联系电话 ：</span>{item.mobile}</div>
                    <div><span>科室 ：</span>{item.smallDeptName || item.bigDeptName}</div>
                    <div><span>诊室 ：</span>{item.diagRoom}</div>
                    <div><span>就诊医生 ：</span>{item.doctorName}</div>
                    <div><span>挂号时间 ：</span>{item.regTime}</div>
										<div><span>签到时间 ：</span>{item.waitingTime}</div>
										<div><span>大屏叫号时间 ：</span>{item.preTime}</div>
                  </div>
                </TabItem>
              )
            })
          }
        </Tabs>
    }

    if(!loadingInfo){
      return <div style={{textAlign:"center", paddingTop:20, paddingBottom:20}}>卡号：{cardno}，没有排队信息</div>;
    }

    return null;
  }
	render(){

		let {display,loadingInfo,userInfos,showTips,tips, cardRenderStatus} = this.state;
    let sexs = {
      "1":"男",
      "2":"女"
    };


    if(display){
      let isView = !!(userInfos && !loadingInfo);
      return (
        <div className="hushitai-shuaka-dialog">
          <Modal
            title="患者信息"
            display={true}
            onOk={isView ? this.onViewInQueue.bind(this) : this.onCancel.bind(this)}
            loading={loadingInfo}
            onCancel={this.onCancel.bind(this)}
            cancelButton={isView}
            okButtonText={isView ? "在队列中查看" : "关闭窗口"}
            cancelButtonText="关闭窗口"
          >
            {this.renderUserInfos()}
            <Loading display={loadingInfo} />
          </Modal>
        </div>
      );
    }else{
      return (
        <div className={cardRenderStatus ? "wait-card" : "wait-card error"} onMouseOver={()=>{this.setState({showTips:true})}} onMouseLeave={()=>{this.setState({showTips:false})}}>
          {
            showTips ? (
              <div className="tooltip tooltip-action tooltip-placement-left">
                <div className="tooltip-arrow"></div>
                <div className="tooltip-inner">
                  {tips}
                </div>
              </div>
            ) : null
          }
          <span>读卡器</span>
        </div>
      )
    }
	}
}
