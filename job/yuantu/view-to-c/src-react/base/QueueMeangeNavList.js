
import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'
import utils from '../../libs/utils'
import QueueCenter from '../../module/QueueCenter';
import MenuQueueNav from './MenuQueueNav'
import Loading from './Loading'
import config from '../../../config';
import TelescopicMenu from './TelescopicMenu'

//分诊管理部分动态导航

export default class QueueMeangeNavList extends React.Component{

  constructor(props){
    super(props)

    this.intervalFlag = {
      "1":"上午",
      "2":"下午"
    }
    this.state = {
      loading:false,
      isOpen:true,
      queueMenu:[]
    }
  }
  componentDidMount() {
    let {corpId, area} = this.props;
    if(corpId && area){
      this.initData(corpId, area);
    }

  }

  componentWillReceiveProps(nextProps) {
    let {corpId, area} = nextProps;
    if(corpId != this.props.corpId || area != this.props.area){
      this.setState({
        loading:true
      });
      this.initData(corpId, area);
    }
  }

  componentWillUnmount() {
    this.timer && this.timer.stop();
  }

  initData(corpId, area){
    this.timer && this.timer.stop();
    this.timer = QueueCenter.getActiveCorpWithOutHierarchy(corpId, area, "menuGetData").subscribe({
      onComplete:()=>{
        this.setState({
          loading:false
        })
      },
      onSuccess:(result)=>{
        this.reloaSideMenu(result.data)
      },
      onError:(result)=>{
        //登录超时
        if(result.resultCode == "403"){
          window.location.hash = "#/login";
        }

        this.reloaSideMenu([]);
      }
    }).turns(5000);
  }

  reloaSideMenu(data){
    //是否排序
    let {corpId, isSort} = this.props;

    //去掉前端排序
    // if(isSort){//对数据排序 科室排到后面，医生按照字母顺序排
    //   data = data.sort((a, b)=>{
    //     if(b.emp && a.emp){//都是医生按照字母排
    //       return b.simplePY < a.simplePY ? 1 : -1;
    //     }
    //
    //     if(!b.emp && !a.emp){//都是医生按照字母排
    //       return b.simplePY < a.simplePY ? 1 : -1;
    //     }
    //
    //     if(a.emp && !b.emp){//b不是医生，不要交换位置
    //       return -1;
    //     }
    //     if(!a.emp && b.emp){
    //       return  1;
    //     }
    //   });
    // }

    let queueMenu = data.map((item)=>{

      let currentPatient = item.currentPatient;
      let queueName = currentPatient ? currentPatient.queueName : "";

      return {
        title:item,
        // title:title,//item.name+(item.allNum || ""),
        href:"#/queue?"+utils.flat({
          queueCode:item.code,
          queueName:queueName || item.name
        }),
        key:item.code,
        children:item.children.map((child)=>{

          return {
            href:"#/queue?"+utils.flat({
              queueCode:item.code,
              doctorCode:child.code,
              doctorName:child.name,
              queueName:queueName || item.name,
            }),
            code:item.code,
            doctorCode:child.code,
            doctorName:child.name,
            queueCode:item.code,
            queueName:queueName || item.name,
            key:child.code + item.code,
            currOrderNo:child.currentPatient ? child.currentPatient.orderNoTag : null
          };

        })
      }
    });

    this.setState({
      queueMenu:queueMenu
    })
  }

  onOpen(){
    this.setState({
      isOpen:!this.state.isOpen
    })
  }

  renderQueueChilds(children){
    let doctorCode = this.props.doctorCode;
    let queueCode = this.props.pathname;//.query.queueCode;

    if(children && children.length){
      return (
        <ul className="last-child">
          {
            children.map((item, index)=>{
              return <li key={index}  className={(doctorCode+queueCode) == item.key ? "action" : ""} >
                <a href={item.href}>
                  {item.doctorName}
                  {item.currOrderNo ?
                    <span  style={{textIndent:0, marginLeft:4}} >
                      <Badge type="weaker">
                        {item.currOrderNo}
                      </Badge>
                    </span>
                    : null
                  }
                </a>
              </li>
            })
          }
        </ul>
      )
    }
    return null
  }
  //渲染动态队列title
  renderQueueTitle( item ){

    if(typeof item == "string"){
      return  item;
    }

    let currentPatient = item.currentPatient;
    let queueName = currentPatient ? currentPatient.queueName : "";
    let title = item.name + (item.emp ? `(${item.smallDeptName})` : "");
    if(item.currentPatient){
      title = <span key="1">
        {title}
        <span style={{textIndent:0, marginLeft:4}} >
          <Badge type="weaker">
            {this.intervalFlag[currentPatient.intervalFlag]} {currentPatient.orderNoTag}
          </Badge>
        </span>
      </span>
    }
    //队列报警
    if(item.isAlarmFlag){
      title = [title,
        <span key="2"  style={{textIndent:0, marginLeft:4}} >
          <Badge>{item.allNum}</Badge>
        </span>
        ];
    }

    return title;
  }

  render(){

    let {pathname} = this.props;
    let {queueMenu,loading,isOpen} = this.state;
    /**
      <div className="filter-time">
        <select >
          <option>全部</option>
          <option>上午</option>
          <option>下午</option>
        </select>
      </div>
    */
    return (
      <div className={isOpen ? "item open" : "item"} data-spm="navqueuelist" >
        <a className="title" href="#/" onClick={this.onOpen.bind(this)} >
          分诊管理({queueMenu.length}) <Loading display={loading}  text={""} style={{display:"inline-block", lineHeight:"2em"}} />
          <div className="trg"></div>
        </a>
        <div className="children">
          {
            queueMenu.length ? (
              <ul>
                {
                  queueMenu.map((item, index)=>{
                    return <TelescopicMenu
                      key={index}
                      href={item.href}
                      isActive={pathname == item.key}
                      title={this.renderQueueTitle(item.title)}
                      childrenMenu={this.renderQueueChilds(item.children)}
                    />
                  })
                }
              </ul>
            ) : null
          }
        </div>
      </div>
    )
  }
}
