/*
 * @Author: saohui 
 * @Date: 2017-09-14 09:35:30 
 * @Last Modified by: saohui
 * @Last Modified time: 2017-09-14 09:54:57
 */

import React from 'react'
import {SmartBlockComponent} from './BaseComponent/index'
import util from './lib/util'
import UserCenter from './module/UserCenter'
import Alert from './component/alert/alert'
import Input from './component/signForm/input'

export default class Index extends SmartBlockComponent {
  constructor(props) {
    super(props)
   
    this.state = {
      success: true 
    }
  }
  
  /* render 区开始 */
  
  render () {
    return <div>
      <h1>首页</h1>
    </div>
  }
}