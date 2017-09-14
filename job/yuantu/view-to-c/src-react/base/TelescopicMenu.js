
import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'
import utils from '../../libs/utils'
import QueueCenter from '../../module/QueueCenter';
import MenuQueueNav from './MenuQueueNav'
import Loading from './Loading'
import config from '../../../config';


//可展开收齐的导航
export default class TelescopicMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      isOpen:false
    }
  }
  render(){
    let {isOpen} = this.state;
    let {href, isActive, title, childrenMenu, smallDeptName} = this.props;
    return (
      <li className={[isActive ? "action" : null, isOpen?"open":null].join(" ")}>
        <a href={href} className={isActive ? "action" : ""} >
          {title}
        </a>
        {
          childrenMenu ? <div className="right-trg" onClick={()=>{ this.setState({isOpen:!this.state.isOpen}) }} >
            <div className="trg"></div>
          </div> : null
        }
        {
          childrenMenu
        }
      </li>
    )
  }
}
