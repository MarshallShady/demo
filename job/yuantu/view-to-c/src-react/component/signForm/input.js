/*
 * @Author: Saohui 
 * @Date: 2017-06-20 09:34:35 
 * @Last Modified by: Saohui
 * @Last Modified time: 2017-07-18 11:12:48
 */
import React from 'react'
import {SmartBlockComponent} from '../../BaseComponent/index'
import './input.less'

/**
 * 
 * props 拥有: bgUrl, placeholder = '', type = 'text', onChange = (react 默认会有), defaultValue = '', showBtn, btnVal = '', btnClick = function(){}
 *       其中: bgUrl 为必填项
 * input.value(获取 value 方式) : 使用 onChange 中可以获得 input 的 event (事件对象)
 *       然后使用 event.target 获取到真实的 input 然后获取 value
 * Re: <Input onChage={ (e)=>{ this.inputValue = e.target.value } } />
 * 当有比如，发送验证码这种需要按钮的需求时，设置 showBtn 为 true 并输入 按钮文字 btnVal btn 的 click 事件为 btnClick
 * @export
 * @class Input
 * @extends {SmartBlockComponent}
 */
export default class Input extends SmartBlockComponent {
  constructor(props){
    super(props)

    const val =  this.props.defaultValue || ''

    this.state = {
      ...this.state
      ,loading: false
      ,success: true
      ,againInput: (val.length > 0)
      ,showDeleteAll: false
    }
  }

  formChange(e, type){
    let result = {}
    if( e.target.value.length > 0 ){
      result['againInput'] = true
    } else {
      result['againInput'] = false
    }
    this.setState(result)
  }

  clearInput( ref ){
    const obj = this.refs[ref]
    obj.value = ''
    obj.focus()
    this.setState({
      againInput: false
    })
    this.props.onChange( {
      target: {
        value: ''
      }
    } )
  }

  render(){
    const {bgUrl, placeholder, type, onChange, defaultValue, showBtn} = this.props
    const {againInput, showDeleteAll} = this.state
    let btnVal = this.props.btnVal || ''
    , btnClick = this.props.btnClick || function(){}
    , btnDisable = this.props.btnDisable == undefined || this.props.btnDisable
    , deviation = showBtn ? btnVal.length + 2 : 0
    if( !isFunction( btnClick ) ){
      throw TypeError('btnClick not function. Please entry function')
    }
    // console.log( 'input props: ', bgUrl, placeholder, type, onChange )
    return <div className="form-grounp">
      <label onFocus={(e)=>{ 
          this.setState({ showDeleteAll: true })
        }} onBlur={()=>{
          setTimeout(()=>{
            this.setState({ showDeleteAll: false })
          })
        }} htmlFor="" style={{ paddingRight: deviation + 'em' }}>
        <input onChange={ (e)=>{ 
          // console.log(againInput, showDeleteAll)
          this.formChange(e, 'input')
          onChange(e)
        } } ref="input" type={type || 'text'} className="form-control" defaultValue={defaultValue || ''} placeholder={placeholder || ''} style={{backgroundImage: `url(${bgUrl})`}}/>
        <span onClick={(e)=>{
          this.clearInput( "input" )
          setTimeout(()=>{
            this.setState({ showDeleteAll: true })
          })
        }} style={{display: againInput && showDeleteAll ? 'block' : 'none', right: deviation + 'em'}} className="again-input"></span>
        { showBtn ? <button onClick={ (e)=>{ btnClick(e) } } className={"btn btn-secondary btn-sm input-btn " + (btnDisable ? 'btn-disabled' :'') }>{ btnVal || '' }</button> : null}
      </label>
    </div>
  }
}

function isFunction(fn) {
   return Object.prototype.toString.call(fn)=== '[object Function]';
}
