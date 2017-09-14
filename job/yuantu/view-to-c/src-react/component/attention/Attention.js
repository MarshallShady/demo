import React, {PropTypes} from 'react';
import util from '../../lib/util';
import UserCenter from '../../module/UserCenter';
import './attention.less';
import config from '../../config';
import {TMS_DOMAIN, PROTOCOL} from '../../config';
import SmartNoBlockComponent from '../../BaseComponent/SmartNoBlockComponent';

export default class Attention extends React.Component {

  static defaultProps = {
    unionId: '29',
  };

  static propTypes = {
    unionId: PropTypes.string,
  };

  constructor(props) {
    super(props);
    this.unionId = this.props.unionId;
    this.state = {
      success: false,
      loading: true,
      result: [],
      showAll: false,
    };
    this.timer = null;
  }

  componentDidMount() {
    if (util.isLogin()) {
      UserCenter.getAttention(this.props.unionId).subscribe(this).fetch();
      this.timer = setInterval(() => {
        UserCenter.getAttention(this.props.unionId).subscribe(this).fetch();
      }, 120000)
    } else {
      this.setState({
        loading: false,
      });
    }
  }

  componentWillUnmount() {
    clearInterval(this.timer);
  }

  onSuccess(result) {
    console.log(result);
    this.setState({
      loading: false,
      success: true,
      result: result.data || [],
    });
  }

  onError() {
    this.setState({
      loading: false
    });
  }

  renderError() {
    return <div></div>
  }

  checkStr(str = '') {
    let result = '';
    try {
      const arr = str.split('#c#');
      arr.length > 1 && arr.forEach((z, i) => {
        if (i % 2 == 0) {
          result = result + z + ' <span style="color: #76acf8">';
        } else {
          result = result + z + '</span> ';
        }
      });

      result = (arr.length > 1 && arr.length % 2 == 0) ? result + '</span>' : result;
      arr.length == 1 ? result = str : null;
      return (
        <div className="attention-content-font" dangerouslySetInnerHTML={{__html: result}}></div>
      );
    } catch (e) {
      return <div className="attention-content-font">{str}</div>
    }
  }

  toNext(data) {
    const {unionId} = this.props;
    const remindType = data.remindType - 0;

    const newQueue = specialConfig.newQueue.corpIds.indexOf(data.corpId.toString()) > -1;
    const queueUrl = newQueue ?
      `${config.TMS_DOMAIN}/tms/h5/transfer.php?transferKey=28&` :
      `${config.TMS_DOMAIN}/tms/h5/queuing.php?`;

    switch (remindType) {
      case 1:
        const urlInfo1 = {
          corpId: data.corpId,
          unionId,
          target: '_blank',
        };
        window.location.href = `${queueUrl}${util.flat(urlInfo1)}`;
        break;
      case 2:
        const urlInfo2 = {
          corpId: data.corpId,
          id: data.pointIdStr,
          unionId: this.unionId,
          target: '_blank',
        };
        const url1 = `./register-details-2.html?${util.flat(urlInfo2)}`;
        const tmsUrl1 = `${PROTOCOL}${TMS_DOMAIN}/tms/h5/transfer.php?transferKey=33&${util.flat(urlInfo2)}`;
        window.location.href = util.isTMS() ? tmsUrl1 : url1;
        break;
      case 3:
        const urlInfo3 = {
          corpId: data.corpId,
          target: '_blank',
          patientId: data.patientId,
          billNo: data.thirdPointId, //待确认参数？
        };
        const url2 = `./pay-detail.html?${util.flat(urlInfo3)}`;
        const tmsUrl2 = `${PROTOCOL}${TMS_DOMAIN}/tms/h5/transfer.php?transferKey=34&${util.flat(urlInfo3)}`;
        window.location.href = util.isTMS() ? tmsUrl2 : url2;
        break;
      case 6:
        const urlInfo6 = {
          corpId: data.corpId,
          unionId,
          target: '_blank',
        };
        window.location.href = `${config.TMS_DOMAIN}/tms/h5/queuing.php?${util.flat(urlInfo6)}`;
        break;
      default:
        break;
    }
  }

  renderItem(data, i) {
    const testStyle = {
      position: 'absolute',
      top: '20px',
      left: '5px',
      width: '10px',
      height: '10px',
      borderRadius: '50%',
      backgroundColor: '#ccc',
    };
    return (
      <li onClick={() => this.toNext(data)} key={i} data-spm={i} className="list-item list-item-head-circle">
        <div style={testStyle}></div>
        <div className="list-content txt-nowrap">
          <div className="attention-title-font">{data.title}</div>
          {this.checkStr(data.content)}
          <div className="attention-time-font">{data.date} {data.remindTypeName}</div>
        </div>
      </li>
    );
  }

  showToggle = () => {
    this.setState({
      showAll: !this.state.showAll,
    });
  }

  render() {
    const {result, showAll, success, loading} = this.state;
    if (success && !loading) {
      const attenionList = showAll ? result : result.slice(0, 3);
      if (util.isLogin() && result.length > 0) {
        return (
          <div className="panel g-space" style={{marginTop: '10px'}}>
            <div className="panel-title">就医提醒({result.length})</div>
            <ul className="list-ord" data-spm="attention">
              {attenionList.map((z, i) => this.renderItem(z, i))}
              {
                result.length > 3 ?
                  <div onClick={this.showToggle} data-spm="more" className="search-toggle">
                    {showAll ? '收起' : '查看更多'}
                  </div> : null
              }
            </ul>
          </div>
        );
      }
      return null;
    } else if(loading) {
      return (
        <div className="render-loading-container">
          <div className="icon-h-loading render-loading-circle"></div>
        </div>
      );
    }
    return null;
  }
}