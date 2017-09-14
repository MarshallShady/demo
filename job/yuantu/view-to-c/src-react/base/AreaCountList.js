


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button,Menu, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'

import Loading from './Loading'
import QueueCenter from '../../module/QueueCenter';


export default class AreaCountList extends React.Component{

  constructor(props) {
    super(props);

    this.state = {
      loading:false,
      areas:[]
    }
  }

  componentDidMount() {
    this.reloadData(this.props);
  }

  componentWillReceiveProps(nextProps) {
    this.reloadData(nextProps)
  }

  componentWillUnmount() {
    this.timer && this.timer.stop()
  }

  reloadData( props ){

    let {corpId} = props;
    //所有科室统计
    this.timer && this.timer.stop()
    this.timer = QueueCenter.getCorpQueueCount(corpId).subscribe({
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
        let keys = Object.keys(result.data)
        this.setState({
          areas:keys.map((key)=>{
            return result.data[key]
          })
        })
      },
      onError:(result)=>{
        console.log(result)
      }
    }).truns(1000*10);

  }

  render(){

    let {areas, loading} = this.state;
    return (
      <div className="queue-list">
        <Loading display={loading} />
        {
          areas.map((item, index)=>{
            return (
              <a className="queue-item" key={index} href={`#/queueManage?area=${item.area}&areaName=${item.areaName}`}>
                <div className="title">
                  <b>{item.areaName}</b>
                  {
                    item.closed == 0 ? <Tag type="succeed">已开诊</Tag> : <Tag type="error">已开诊</Tag>
                  }
                </div>
                <div className="context">
                  <div className="number-count">
                    <div className="number">{item.currentOrderNo || "无"}</div>
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
    )
  }

}
