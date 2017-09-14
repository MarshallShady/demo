


import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select,Menu} from 'react-piros'
import utils from '../../libs/utils'
import QueueCenter from '../../module/QueueCenter';


export default class MenuQueueNav extends Menu{



  renderItem(menu){
    return (
      <a className="title" href={menu.href || "javascript:;"}  >
        {menu.title}
        {menu.list && menu.list.length ? <div className="trg"></div> : null}
      </a>
    )
  }

  //判断grouplist的class
  getListGroupClassName(menu){
    let openKeys = this.props.openKeys || [];
    let selectedKeys = this.props.selectedKeys || [];
    let isChildList = !!(menu.list && menu.list.length);
    let isChecked = selectedKeys.indexOf(menu.key) != -1;
    let className = ["item"];

    //有子元素，或者子元素被选中状态
    // console.log( isChildList &&  )
    if(
      isChildList
      && menu.list.filter((item)=>{ return selectedKeys.indexOf(item.key) !=-1 }).length
    ){

      className.push("open");
      className.push("action");
    }

    if(isChecked){//自己被选中
      className.push("action")
    }


    return className.join(" ")
  }


  renderSubItems(list){

    let {selectedKeys} = this.props;
    return (
      <ul>
        {
          list.map((item, index)=>{
            return <li className={selectedKeys.indexOf(item.key) != -1 ? "action" : ""} key={index}>
              <a href={item.href || "javascript:;"}  >
              {item.title}
              </a>
            </li>
          })
        }
      </ul>
    )

  }
  render(){

    let {dataSource} = this.props;

    return (
      <div className="queue-nav-list">
        {
          dataSource.map((menu, index)=>{
            return (
              <div className={this.getListGroupClassName(menu)} key={index} >
                {
                  this.renderItem(menu)
                }
                {
                  menu.list ? this.renderSubItems(menu.list) : null
                }
              </div>
            )
          })
        }
      </div>
    )
  }

}
