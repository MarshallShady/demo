
import React from 'react'
import {render} from 'react-dom'
import {Tag, Button, Table, Badge, Input, Popconfirm,Modal,Select} from 'react-piros'
import utils from '../../libs/utils'
import QueueCenter from '../../module/QueueCenter';
import MenuQueueNav from './MenuQueueNav'
import Loading from './Loading'
import config from '../../../config';


export default class MenuItem extends React.Component{
  constructor(props) {
    super(props);
    this.state = {
      isOpen:true
    }
  }

  onOpen(){
    this.setState({
      isOpen:!this.state.isOpen
    })
  }

  render(){
    let {title, list, pathname} = this.props;
    let {isOpen} = this.state;

    return (
      <div className={isOpen ? "item open" : "item"}   data-spm="navfunclist">
        <a className="title"  onClick={this.onOpen.bind(this)}  >
          {title}
          <div className="trg"></div>
        </a>
        <div className="children">
          <ul>
            {
              list.map((item)=>{
                return <li key={item.key} className={pathname == item.key ? "action" : ""} ><a href={item.href}>{item.title}</a></li>
              })
            }
          </ul>
        </div>
      </div>
    )
  }
}
