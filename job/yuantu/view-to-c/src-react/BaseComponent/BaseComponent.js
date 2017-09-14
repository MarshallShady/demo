
import React from 'react'
import util from '../lib/util'


export default class BaseComponent extends React.Component {

  onSendBefore(){
    this.setState({
      loading:true
    })
  }

  onComplete(){
    this.setState({
      loading:false
    })
  }

}
