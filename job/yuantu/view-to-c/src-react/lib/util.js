import url from 'url';
import hybridAPI from './hybridAPI'
import config from '../config'

let util = {
  query: function () {
    const urlInfo = url.parse(window.location.href, true).query;
    for(let key in urlInfo) {
      if (urlInfo[key] instanceof Array) {
        urlInfo[key] = urlInfo[key][0]
      }
    }
    return urlInfo;
  },
  pad: function (string, length, pad) {
    var len = length - String(string).length
    if (len < 0) {
      return string;
    }
    var arr = new Array(length - String(string).length || 0)
    arr.push(string);
    return arr.join(pad || '0');
  },
  flat: function (param) {
    var str = ""
    for (var key in param) {
      str += key + "=" + (param[key] != undefined ? encodeURIComponent(param[key]) : "") + "&"
    }
    return str.slice(0, -1);
  },
  https: function (a) {
    return String(a).replace("http://", "//");
  },
  //脱敏处理字符串
  sensitive:function(str, length){
    return str.slice(0, length/2) + "****" + str.slice(-length/2);
  },
  //可视化object把假值转换为 ""
  vis: function (json) {
    for (var key in json) {
      // || isNaN(json[key])
      if (json[key] === undefined || json[key] === null) {
        json[key] = "";
      }
    }
    return json;
  },
  forEach(object, callback){
    var list = []
    for (var key in object) {
      list.push(callback(object[key], key))
    }
    return list;
  },
  flatStr: function (str, param) {
    return str + this.flat(param);
  },

  is: function (is, str) {
    return is ? str : null
  },
  rmb: function (rmb, dot) {

    dot = dot == undefined ? 4 : dot;

    if (rmb == 0) {
      return 0
    }

    if (dot == 2) {
      return (rmb).toFixed(2)
    }

    if (dot == 4 && (rmb).toFixed(2) * 10000 == (rmb).toFixed(4) * 10000) {
      return (rmb).toFixed(2);
    }

    return (rmb).toFixed(dot);
  },
  getSID: function () {


    if (this.isLogin()) {
      var cookie = document.cookie;
      var sids = cookie.match(/sid=(\w+)/);
      if (sids && sids[1]) {
        return sids[1];
      }
    }

    return null;
  },

  getUID: function () {

    if (this.isLogin()) {
      var cookie = document.cookie;
      var sids = cookie.match(/[^u]uid=(\d+)/);
      if (sids && sids[1]) {
        return sids[1];
      }
    }

    return null;
  },
  getTID: function () {

    if (this.isLogin()) {
      var cookie = document.cookie;
      var sids = cookie.match(/tid=([\-\w]+)/);
      if (sids && sids[1]) {
        return sids[1];
      }
    }
    return null;
  },
  dateFormatGMT: function (source, pattern) {
    let time = new Date(source);
    time.setUTCHours(time.getUTCHours() + ((time.getTimezoneOffset() + 8 * 60) / 60));
    return this.dateFormat(time, pattern);
  },
  dateFormat: function (source, pattern) {
    // Jun.com.format(new Date(),"yyyy-MM-dd hh:mm:ss");
    //Jun.com.format(new Date(),"yyyy年MM月dd日 hh时:mm分:ss秒");
    if (!source) {
      return "";
    }

    source = new Date(source);
    var pad = this.pad,
      date = {
        yy: String(source.getFullYear()).slice(-2),
        yyyy: source.getFullYear(),
        M: source.getMonth() + 1,
        MM: pad(source.getMonth() + 1, 2, '0'),
        d: source.getDate(),
        dd: pad(source.getDate(), 2, '0'),
        h: source.getHours(),
        hh: pad(source.getHours(), 2, '0'),
        m: source.getMinutes(),
        mm: pad(source.getMinutes(), 2, '0'),
        s: source.getSeconds(),
        ss: pad(source.getSeconds(), 2, '0')
      };
    return (pattern || "yyyy-MM-dd hh:mm:ss").replace(/yyyy|yy|MM|M|dd|d|hh|h|mm|m|ss|s/g, function (v) {
      return date[v];
    });

  },
  //将金额*100的整数转换成 千分位金额 1,234.00
  moneyFormat: function (money, fixed) {
    if (!fixed) {
      fixed = 2
    }
    if (!money || Number(money) == 0) {
      return 0;//(0).toFixed(fixed)
    }
    var negative = false;

    var mStr = money.toString().split(".");
    mStr[1] = Number('0.00' + (mStr[1] || ""));

    if (mStr[0].indexOf('-') == 0) {
      negative = true;
      mStr[0] = mStr[0].replace("-", '');
    }

    var len = mStr[0].length;

    if (len <= 5) {
      if (negative) {
        return '-' + (mStr[0] / 100 + mStr[1]).toFixed(fixed)
      } else {
        return (mStr[0] / 100 + mStr[1]).toFixed(fixed)
      }
    }

    var decimal = (Number('0.' + mStr[0].slice(-2)) + mStr[1]).toFixed(fixed).replace("0.", "");
    var num = [];
    for (var i = -5; i > -len - 3; i = i - 3) {
      var part = [];
      part[0] = mStr[0].slice(i, i + 3);
      num = part.concat(num);
    }
    var round = num.join(",");
    //return (round + '.' + decimal );
    if (negative) {
      return ('-' + round + '.' + decimal );
    } else {
      return (round + '.' + decimal );
    }
  },

  //获取标准时差
  getTimezoneOffset: function () {
    var now = new Date();
    return now.getTimezoneOffset() * 60 * 1000;
  },
  isLogin: function () {
    return /sid\=\w+/.test(document.cookie);
  },

  isInYuantuApp: function () {

    //临时
    //return navigator.userAgent.indexOf("MicroMessage") != -1;
    return navigator.userAgent.indexOf("YuanTu(") != -1;
    // return navigator.userAgent.indexOf("YuanTu(") != -1;
  },
  //在微信中
  isInMicroMessenger: function () {
    return navigator.userAgent.indexOf("MicroMessenger") != -1;
  },
  isInAliPay: function () {
    return navigator.userAgent.indexOf("AliPay") != -1;
  },
  waitAlert: function (text, delay) {
    /**
     700 毫秒以上才弹出加载动画
     */
    var self = this;
    this.waitHide();
    timeout = setTimeout(function () {
      self.alertDialog({
        text: text,
        time: 120000,
        isWait: true
      });
    }, delay === undefined ? 700 : delay);
  },
  waitHide: function () {
    timeout && clearTimeout(timeout);
    if (alerts) {
      alerts.remove();
    }
  },
  alert: function (text, callback) {
    this.alertDialog({
      text: text,
      time: Math.max(String(text).length * 250, 2000)
    }, callback);
  },
  /**
   * text
   * param {
				text:"",
				time:"", //显示时间
				isWait:true //是否显示等待
			}
   */
  alertDialog: function (param, callback) {

    timeout && clearTimeout(timeout);

    var text = param.text;
    var time = param.time;
    var isWait = param.isWait;
    // console.log( param )
    if (alerts) {
      alerts.remove();
      alerts = null;
    }

    alerts = $('<div class="ui-alert"><div  class="text">' + (isWait ? '<div class="loading-icon"></div>' : '') + text + '</div></div>');

    alerts.appendTo(document.body);

    setTimeout(function () {
      callback && callback();
      alerts && (alerts.remove());
    }, time || text.length * 200);

    return alerts;
  },
  dialog: function (content, callback, option) {
    option = $.extend({
      cancel: true,
      cancelText: "取消",
      ok: true,
      okText: "确定"
    }, option || {})

    var tmpl = '<div class="ui-dialog show">' +
      '<div class="ui-dialog-cnt">' +
      '<div class="ui-dialog-bd">' +
      '<div style="text-align:center;">' + content + '</div>' +
      '</div>' +
      '<div class="ui-dialog-ft">' +
      (option.cancel ? '<button type="button" class="J_Btn" data-role="0">' + option.cancelText + '</button>' : '') +
      (option.ok ? '<button type="button" class="J_Btn" data-role="1">' + option.okText + '</button>' : '') +
      '</div>' +
      '</div>' +
      '</div>';

    var dialog = $(tmpl);
    dialog.appendTo(document.body).find(".J_Btn").click(function () {
      var role = $(this).attr("data-role");
      dialog.remove();
      callback && callback(role == "1");
    });

  },
  //调用jsbrige方法
  brige: function (name, param, callback, fail, time) {

    if (this.isInYuantuApp()) {

      lib.windvane.call("jsbrige", name, param || {}, callback || function () {
        }, fail || function () {
        }, time);
    } else {

      // 不是YuantuApp时不调用jsbrige

      return false;
    }
  },
  // return https://s.yuantutech.com/yuantu/h5-cli/1.0.3/a.html
  getCurrentSitePath(){

    let pathName = location.pathname.match(/\/yuantu\/h5-cli\/[\d\.]+\//);
    pathName = pathName ? pathName[0] : "/";

    return location.origin + pathName;

  },
  //跳转到登录页面 history 是否清楚当前页面的历史记录
  goLogin: function (clearHistory) {
    // var h5_domain = config.h5Domain;
    var query = url.parse(window.location.href, true).query || {};
    var unionId = query.unionId || '';
    var corpId = query.corpId || '';
    var loginUrl = this.h5URL("/sign-in.html?");//"https://"+h5_domain+"/tms/h5/login.php";
    loginUrl += this.flat({
      backview:1,
      corpId:query.corpId,
      unionId:query.unionId,
      redirecturl:window.location.href
    });

    if (this.isInYuantuApp()) {
      loginUrl = loginUrl.replace("/sign-in.html?", "/pages/login.html?");
      hybridAPI.openView(loginUrl, "bottom-in");
    } else {
      if (history) {
        window.location.replace(loginUrl);
      } else {
        window.location.href = loginUrl;
      }
    }
  },


  //返回上一个webview
  goBack: function (isReload, url) {
    console.log(isReload)
    // lib.windvane.call("jsbrige","backView", {isReload:isReload});
    if (this.isInYuantuApp()) {
      // this.brige("backView", {isReload: isReload});
      hybridAPI.backView(isReload);
    } else {

      //直接back无法刷新上一个页面， replace 会导致需要返回两次
      // if(isReload && document.referrer){
      // window.location.replace(document.referrer);
      // }else{
      window.history.back();
      // }
    }
  },
  //支付宝支付
  pay: function (feeChannel, param, callback, fail) {
    this.brige("pay", {
      feeChannel: feeChannel,//1 支付宝， 2微信支付
      payData: param
    }, callback, fail);
  },
  getYuanTuVersion: function () {
    //getPlatform
    // > gt
    // < lt
    this.getPlatform();
  },
  setNativeTitle: function (text) {
    return hybridAPI.setTitle(text);
  },
  getDeviceToke: function (callback, fail) {

    if (this.isInYuantuApp()) {
      hybridAPI.getDeviceToken()
        .then( callback, fail )
    } else {
      fail()
    }
  },
  getPlatform: function () {
    var ua = navigator.userAgent
    if (ua.indexOf("iPhone") != -1 || ua.indexOf("iPad") != -1) {
      return "ios";
    }

    if (ua.indexOf("Android") != -1) {
      return "android";
    }
    return null;
  },
  //当前客户端版本比较
  version: {
    v: (function () {

      var versions = navigator.userAgent.match(/YuanTu\((?:QY|YY)\/([\d.]+)\)/);
      if (versions && versions.length) {
        return {
          x: parseInt(versions[1].split(".")[0]),
          y: parseInt(versions[1].split(".")[1]),
          z: parseInt(versions[1].split(".")[2])
        }
      }

      return {x: 0, y: 0, z: 0};
    })(),
    // >
    gt: function (x, y, z) {
      var aa = [this.v.x, this.v.y, this.v.z]
      var ab = [x, y, z];
      var i = 0;
      var la = aa.length, lb = ab.length;
      while (la > lb) {
        ab.push(0);
        ++lb;
      }
      while (la < lb) {
        aa.push(0);
        ++la;
      }
      while (i < la && i < lb) {
        var ai = parseInt(aa[i], 10);
        var bi = parseInt(ab[i], 10);
        if (ai > bi) {
          return true;
        } else if (ai < bi) {
          return false;
        }
        ++i;
      }

      return false;
    },
    // <
    lt: function (x, y, z) {
      // 相等的时候不小于
      if (this.v.x == x && this.v.y == y && this.v.z == z) {
        return false;
      }

      return !this.gt(x, y, z)
    },
    test: function () {
      var v = {
        v: {
          x: 2,
          y: 1,
          z: 19
        }
      }

      console.log(this.version.gt.apply(v, [2, 1, 6]) == true)
      console.log(this.version.gt.apply(v, [3, 1, 6]) == false)
      console.log(this.version.gt.apply(v, [2, 2, 6]) == false)
      console.log(this.version.gt.apply(v, [2, 1, 19]) == false)
      // console.log( this.version.test() )
      return [this.v.x, this.v.y, this.v.z].join(".") > [x, y, z].join(".")
    },
  },
  h5Version: {
    v: (function () {

      var versions = window.location.href.match(/\/h5\-cli\/([\d\.]+)\/pages/);
      if (versions && versions.length) {
        return {
          x: parseInt(versions[1].split(".")[0]),
          y: parseInt(versions[1].split(".")[1]),
          z: parseInt(versions[1].split(".")[2])
        }
      }
      return {x: 100, y: 100, z: 100};
    })(),
    // >
    gt: function (x, y, z) {
      var aa = [this.v.x, this.v.y, this.v.z]
      var ab = [x, y, z];
      var i = 0;
      var la = aa.length, lb = ab.length;
      while (la > lb) {
        ab.push(0);
        ++lb;
      }
      while (la < lb) {
        aa.push(0);
        ++la;
      }
      while (i < la && i < lb) {
        var ai = parseInt(aa[i], 10);
        var bi = parseInt(ab[i], 10);
        if (ai > bi) {
          return true;
        } else if (ai < bi) {
          return false;
        }
        ++i;
      }

      return false;
    },
    // <
    lt: function (x, y, z) {
      // 相等的时候不小于
      if (this.v.x == x && this.v.y == y && this.v.z == z) {
        return false;
      }
      return !this.gt(x, y, z)
    }
  },

  IsPC: function () {
    var userAgentInfo = navigator.userAgent;
    var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
    var flag = true;
    for (var v = 0; v < Agents.length; v++) {
      if (userAgentInfo.indexOf(Agents[v]) > 0) {
        flag = false;
        break;
      }
    }
    return flag;
  },

  //根据经纬度计算距离
  getFlatternDistance: function (lat1 = '', lng1 = '', lat2 = '', lng2 = '') {

    if (lat1 && lat2 && lng1 && lng2) {
      lat1 = lat1 - 0;
      lat2 = lat2 - 0;
      lng1 = lng1 - 0;
      lng2 = lng2 - 0;

      var EARTH_RADIUS = 6378137.0;    //单位M
      var PI = Math.PI;

      function getRad(d) {
        return d * PI / 180.0;
      }

      var f = getRad((lat1 + lat2) / 2);
      var g = getRad((lat1 - lat2) / 2);
      var l = getRad((lng1 - lng2) / 2);

      var sg = Math.sin(g);
      var sl = Math.sin(l);
      var sf = Math.sin(f);

      var s, c, w, r, d, h1, h2;
      var a = EARTH_RADIUS;
      var fl = 1 / 298.257;

      sg = sg * sg;
      sl = sl * sl;
      sf = sf * sf;

      s = sg * (1 - sl) + (1 - sf) * sl;
      c = (1 - sg) * (1 - sl) + sf * sl;

      w = Math.atan(Math.sqrt(s / c));
      r = Math.sqrt(s * c) / w;
      d = 2 * w * a;
      h1 = (3 * r - 1) / 2 / c;
      h2 = (3 * r + 1) / 2 / s;

      var distance = d * (1 + fl * (h1 * sf * (1 - sg) - h2 * (1 - sf) * sg));
      return distance;
    }
    //传入数据有误则返回-100
    return -100;
  },
  distanceFormat: function (d) {
    d = d - 0;
    if (d >= 0) {
      const distance = (d / 1000).toFixed(1) + 'km';
      return distance;
    }
    return '';
  },
  //浏览器是否支持isSticky属性
  isSticky: function () {
    let dom = document.createElement('div');
    const sticky = ['-webkit-sticky', 'sticky'];
    sticky.map(z => {
      dom.style.position = z;
    });
    const support = dom.style.position.indexOf('sticky') > -1;
    dom = null;
    return support;
  },
  isToday: function (day) {
    if (day) {
      const today = new Date();
      const theDay = new Date(day);
      const year = today.getFullYear();
      const month = today.getMonth();
      const date = today.getDate();
      const tyear = theDay.getFullYear();
      const tmonth = theDay.getMonth();
      const tdate = theDay.getDate();
      return year == tyear && month == tmonth && date == tdate;
    }
    return false;
  },
  isWeixin: function () {
    const ua = window.navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == 'micromessenger') {
      return true;
    } else {
      return false;
    }
  },
  isTMS: function() {
    const url = window.location.href;
    return url.indexOf('yuantutech.com/tms/') > -1;
  },
  h5URL(path){
    return location.protocol + "//" + config.H5_DOMAIN + "/yuantu/h5-cli/" + config.VERSION + path;
  },
  tmsUrl(path){
    return location.protocol+"//"+config.H5_DOMAIN+path;
  }

  // 把多维数组进行铺平
  ,arrDeepFlattenL: function deepFlatten(arr) {
     const flatten = (arr)=> [].concat(...arr); return flatten(arr.map(x=>Array.isArray(x)? deepFlatten(x): x))
  }

  ,translateHtmlToTxt ( html ) {
    const dom = document.createElement('div')
    dom.innerHTML = html
    return dom.textContent
  }
}

window.nihao = util;
export default util;

// module.exports = util;
