/*
 * @Author: saohui 
 * @Date: 2017-08-18 10:54:37 
 * @Last Modified by: saohui
 * @Last Modified time: 2017-08-31 16:11:20
 */
import React, { Component, PropTypes } from 'react'
import './PullUpRefresh.less'


/**
 * 
 * 下拉刷新（ 可用于分页 ）
 * 此组件之后，不要有元素，因为此组件是基于 body 的滚动条简体
 * 当翻页的时候，请自行进行记录当前
 * @export props.isFinished true / false 用于判断，是否还需进行下拉刷新，当为 false 时候，拉倒底下会有默认的 loading
 * @export props.onNextPage function 当进行下拉刷新的时候，调用此事件，此事件的参数是 已经被刷新过几次，但是此数字和第几页不同，因为有可能有请求失败
 * @export props.loadingHtml html 自定义 loading 样式
 * @export props.finishedHtml html 自定义，没有数据的提示
 * @export props.childen html 正文 html 的内容
 * @export props.className 容器的 className
 * @class PullUpRefresh
 * @extends {Component}
 */
export default class PullUpRefresh extends Component {

  static propTypes = {
    isFinished: PropTypes.bool
    ,onNextPage: PropTypes.func
    ,loadingHtml: PropTypes.element
    ,finishedHtml: PropTypes.element
  }

  constructor ( props ) {
    super( props )
    
    this.state = {
      currentPage: 0
      ,pageSize: props.initPageSize || 10
      ,loadLock: false
      ,isFinished: false
      ,packingLoadCtx: ctx => this.packingLoadCtx( ctx )
    }
    this.nextPageMethod = {
      onFinished: () => this.setFinished( true )
      ,continueLoad: () => this.setFinished( false )
    }
  }
  packingLoadCtx ( ctx ) {
    const { state, nextPageMethod } = this

    state.loadLock = true
    return {
      onSuccess: ( result ) => {
        state.loadLock = false

        if ( result.success ) {
          state.currentPage++
        }

        ctx.onSuccess && ctx.onSuccess( result, nextPageMethod )
      }
      ,onError: ( result ) => {
        ctx.onError && ctx.onError( result )
      }
      ,onSendBefore: () => {
        ctx.onSendBefore && ctx.onSendBefore()
      }
      ,onComplete: () => {
        ctx.onComplete && ctx.onComplete()
      }
    }
  }
  setFinished ( val ) {
    this.setState({
      isFinished: val
    })
  }
  
  componentDidMount () {
    this.regEventScroll()

    // 默认先去加载第一页
    const { onNextPage } = this.props
    const { state } = this
    onNextPage && onNextPage( Object.assign( state ))
  }
  componentWillReceiveProps () {
    this.regEventScroll()
  }

  regEventScroll () {
    const { isFinished } = this.state
    if ( isFinished ) {
      window.onscroll = null
    } else {
      window.onscroll = this.myScroll.bind( this )
    }
  }

  myScroll () {
    const { onNextPage } = this.props
    const { state } = this

    clearTimeout( this.timer )

    this.timer = setTimeout(() => {
      const scrollHeight = document.body.scrollHeight
      const scrollTop = document.body.scrollTop
      const screenHeight = window.screen.height
      
      if ( scrollHeight - scrollTop < screenHeight + 20 ) {
        state.refreshNum ++

        !state.loadLock && onNextPage && onNextPage( Object.assign( state ))
      }
    }, 100 )
  }

  /***** render 区开始 *****/
  
  renderLoading () {
    const { props } = this
    return props.loadingHtml ? 
      props.loadingHtml : <div className="pull-up-fresh-after">
      <span className="icon-h-loading "></span> 
      <span className="txt-info">加载中...</span>
    </div>
  }
  renderFinished () {
    const { props } = this
    return props.finishedHtml ? 
      props.finishedHtml : <div className="pull-up-fresh-after">
      <span className="txt-info">已经没有更多内容了</span>
    </div>
  }

  renderBody () {
    const { props } = this
    return <div>
      { props.children }
    </div>
  }
  renderFooter () {
    const { state } = this
    return !state.isFinished ? this.renderLoading() : this.renderFinished()
  }

  render () {
    const { className } = this.props
    return <div className={'pull-up-fresh '+ ( className || '')}>
      { this.renderBody() }
      { this.renderFooter() }
    </div>
  }
}