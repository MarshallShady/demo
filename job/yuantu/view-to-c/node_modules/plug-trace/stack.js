var urlLib = require("url");

Date.prototype.format = function (fmt) {
  var o = {
    "M+": this.getMonth() + 1,               //月份
    "d+": this.getDate(),                    //日
    "h+": this.getHours(),                   //小时
    "m+": this.getMinutes(),                 //分
    "s+": this.getSeconds(),                 //秒
    "q+": Math.floor((this.getMonth() + 3) / 3), //季度
    "S": this.getMilliseconds()              //毫秒
  };
  if (/(y+)/.test(fmt)) {
    fmt = fmt.replace(RegExp.$1, (this.getFullYear().toString()).substr(4 - RegExp.$1.length));
  }
  for (var k in o) {
    if (new RegExp('(' + k + ')').test(fmt)) {
      fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(o[k].toString().length)));
    }
  }
  return fmt;
};

exports = module.exports = function (module_name) {
  var trace = [];

  function push(obj) {
    obj = obj || {};
    obj.time = obj.time || new Date().format("hh:mm:ss.S");
    trace.push(obj);
  }

  return {
    request: function (host, files) {
      push({
        type: "request",
        host: host,
        list: files
      });
    },
    response: function (input, content) {
      push({
        type: "response",
        url: input,
        content: content
      });
      process.emit(module_name, trace);
    },
    error: function (input, tip) {
      push({
        type: "error",
        msg: input,
        tip: tip || "Exception"
      });
    },
    warn: function (input, tip) {
      push({
        type: "warn",
        msg: input,
        tip: tip || "Exception"
      });
    },
    info: function (input, tip) {
      push({
        type: "info",
        msg: input,
        tip: tip || "Untitled"
      });
    },
    engine: function (url, path) {
      push({
        type: "engine",
        url: url,
        path: path
      });
    },
    local: function (url, path) {
      push({
        type: "local",
        url: url,
        path: path
      });
    },
    filter: function (regx, ori_url, url) {
      push({
        type: "filter",
        regx: regx,
        before: ori_url,
        after: url
      });
    },
    cache: function (url, path) {
      push({
        type: "cache",
        url: url,
        path: path
      });
    },
    remote: function (url, hosts) {
      url = /^https?:\/\//.test(url) ? url : "http:" + url;
      var host = urlLib.parse(url).host;
      if (typeof hosts == "object") {
        host = hosts[host] ? hosts[host] : host;
      }
      else if (typeof hosts == "string") {
        host = hosts;
      }
      push({
        type: "remote",
        url: url,
        host: host
      });
    }
  }
};
