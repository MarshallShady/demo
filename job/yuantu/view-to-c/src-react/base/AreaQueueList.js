


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button,Menu, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'
import utils from '../../libs/utils'
import Loading from './Loading'
import QueueCenter from '../../module/QueueCenter';


export default class AreaQueueList extends React.Component{

  constructor(props) {
    super(props);

    this.state = {
      filter:null,
      loading:false,
      queue:[]
    }
  }


  componentDidMount() {
    let {corpId, area} = this.props;
    this.reloadList(corpId, area);

  }

  componentWillReceiveProps(nextProps) {
    let {corpId, area} = nextProps;
    this.reloadList(corpId, area);
  }

  componentWillUnmount() {
    this.queryAjax && this.queryAjax.stop();
  }

  reloadList(corpId, area){
    this.queryAjax = QueueCenter.getActiveCorpWithOutHierarchy(corpId, area, "areaReloadList").subscribe({
      onSendBefore:()=>{
        this.setState({
          loading:true
        })
      },
      onComplete:()=>{
        this.setState({
          loading:false
        })
      },
      onSuccess:(result)=>{
        this.setListData(result.data)

      },
      onError:(result)=>{
        this.setListData([]);
      }
    }).turns(5000);
  }

  setListData(data){
    let queue = data.map((item)=>{
      return {
        queueCode:item.code,
        queueName:item.queueName || item.name,
        simplePY:item.simplePY,
        py:item.py,
        currentOrderNo:item.currentPatient ? utils.pad(item.currentPatient.orderNo, 2) : "-", //当前呼叫号码
        completeNum:item.completeNum,// 已就诊人数
        waitNum:item.waitNum,// 候诊中
        closed:item.closedFlag,// 1已关闭  0 正常
      }
    });

    this.setState({
      queue:queue
    })

  }


  onFilterKeyChange(){
    this.setState({
      filter:this.refs["filter"].value
    })
  }

  render(){

    let {queue,filter,loading} = this.state;
    let completeNum = 0;
    let waitNum = 0;
    queue = queue.filter((item)=>{
      completeNum += item.completeNum;
      waitNum += item.waitNum;
      if(filter){
        return item.queueName.indexOf(filter) != -1 || item.simplePY.indexOf(filter) != -1 || item.py.indexOf(filter) != -1;
      }
      return true;

    });

    return (
      <div>
        <div className="triage-count" style={{width:"49%"}}>
          <div className="item no-hover">
            <div className="icon ing"></div>
            <div className="number">{waitNum}</div>
            <div className="text">当前侯诊中人数</div>
          </div>
          <div className="item no-hover">
            <div className="icon count"></div>
            <div className="number">{completeNum}</div>
            <div className="text">今日已就诊人数</div>
          </div>
        </div>
        <div className="con-title">
          诊区队列
          <div style={{width:250, marginLeft:20 ,display:"inline-block"}}  ><Input type="text"  placeholder="队列名首字母过滤队列" ref="filter" onChange={this.onFilterKeyChange.bind(this)} style={{margin:0}} /></div>
          <div style={{display:"inline-block", width:120, fontSize:12, lineHeight:"12px"}}><Loading display={loading} /></div>
        </div>
        <div className="queue-list" data-spm="area-queuelist">
          {
            queue.length == 0 && !loading ?
              <div className="alert alert-info">
                  <span className="alert-message">没有队列数据</span>
              </div>
            : null
          }
          {
            queue.map((item, index)=>{
              return (
                <a className="queue-item" key={index} href={"#/queue?"+utils.flat({queueCode:item.queueCode, queueName:item.queueName})} >
                  <div className="title">
                    <b>{item.queueName}</b>
                    {
                      item.closed == 0 ? <Tag type="succeed">已开诊</Tag> : <Tag type="error">未开诊</Tag>
                    }
                  </div>
                  <div className="context">
                    <div className="number-count">
                      <div className="number">{item.currentOrderNo}</div>
                      <div className="text">当前叫号</div>
                    </div>
                    <div className="number-count">
                      <div className="number">{item.waitNum}人</div>
                      <div className="text">候诊中</div>
                    </div>
                    <div className="number-count">
                      <div className="number">{item.completeNum}</div>
                      <div className="text">已就诊</div>
                    </div>
                  </div>
                </a>
              )
            })
          }

        </div>
      </div>
    )
  }

}
