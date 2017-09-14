
import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'
import utils from '../../libs/utils'
import QueueCenter from '../../module/QueueCenter';
import MenuQueueNav from './MenuQueueNav'
import Loading from './Loading'
import config from '../../../config';
import QueueMeangeNavList from './QueueMeangeNavList'
import MenuItem from './MenuItem'

export default class SideMenu extends React.Component{
  constructor(props) {
    super(props);

    this.isSortQueue = this.props.isSortQueue || false;//不排序
    let menus = [
      {
        title:"诊室管理",
        key:"/daigRoomManage",
        href:"#/daigRoomManage"
      }
    ];

    // let func_room_scheduling = false; //是否需要诊室排班
    // let func_menpai = false; //门牌管理
    // let func_duilie = false; //队列管理


    if(config.func_room_scheduling){
      menus.push({
        title:"诊室排班",
        key:"/schedule",
        href:"#/schedule"
      });
    }

    if(config.func_menpai){
      menus.push({
          title:"门牌管理",
          key:"/doorplateManage",
          href:"#/doorplateManage"
        });
    }

    if(config.func_duilie){
      menus.push({
          title:"队列管理",
          key:"/queue-admin",
          href:"#/queue-admin"
        });
    }


    this.state = {
      homeMenu:{
        title:"首页",
        href:"#/",
        key:"/"
      },
      queueMenu:{
        title:"分诊管理",
        key:"/",
        href:"#/",
        list:[]
      },
      doctorInfoMenu:{
        title:"医护人员信息管理",
        key:"/doctorInfo",
        href:"#/doctorsList",
      },
      dataMenu:{
        title:"数据维护",
        key:"/schedule",
        list:menus
      }
    };




  }



  render(){

    let {homeMenu, queueMenu, doctorInfoMenu, dataMenu} = this.state;
    let dataSource = [queueMenu, doctorInfoMenu, dataMenu];//homeMenu,
    let {pathname} = this.props.location;
    let doctorCode = null
    let {area, corpId} = this.props;

    if(pathname == "/queue"){
      pathname = this.props.location.query.queueCode;
      doctorCode = this.props.location.query.doctorCode;
    }

    // return <MenuQueueNav dataSource={dataSource}  defaultOpenKeys={["/fenzguanli", "/scheduleMenage"]} selectedKeys={[pathname]} ></MenuQueueNav>
    let dataMenuTitle = <span>{dataMenu.title}</span>
    return (
      <div className="queue-nav-list" >
        <QueueMeangeNavList pathname={pathname} area={area} doctorCode={doctorCode} corpId={corpId} isSort={this.isSortQueue} />
        <div className="item"  >
          <a className="title" href="#/doctorsList" data-spm="yihurenyuan">
            医护人员信息管理
          </a>
        </div>
        <MenuItem pathname={pathname}  title={dataMenuTitle} list={dataMenu.list} />
      </div>
    )
  }
}
