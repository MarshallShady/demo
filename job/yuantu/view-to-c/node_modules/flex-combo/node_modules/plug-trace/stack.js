"use strict";

const urlLib = require("url");

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

class Stack {
  constructor(module_name) {
    this.fireName = module_name;
    this.traceStack = [];
  }

  push(obj) {
    obj = obj || {};
    obj.time = obj.time || new Date().format("hh:mm:ss.S");
    this.traceStack.push(obj);
  }

  request(host, files) {
    this.push({
      type: "request",
      host: host,
      list: files
    });
  }

  response(url) {
    this.push({
      type: "response",
      url: url
    });

    process.emit(this.fireName, this.traceStack);
  }

  fail(url) {
    this.push({
      type: "fail",
      url: url
    });
    process.emit(this.fireName, this.traceStack);
  }

  error(input, tip) {
    this.push({
      type: "error",
      msg: input,
      tip: tip || "Exception"
    });
  }

  warn(input, tip) {
    this.push({
      type: "warn",
      msg: input,
      tip: tip || "Exception"
    });
  }

  info(input, tip) {
    this.push({
      type: "info",
      msg: input,
      tip: tip || "Untitled"
    });
  }

  engine(url, path) {
    this.push({
      type: "engine",
      url: url,
      path: path
    });
  }

  local(url, path) {
    this.push({
      type: "local",
      url: url,
      path: path
    });
  }

  filter(regx, ori_url, url) {
    this.push({
      type: "filter",
      regx: regx,
      before: ori_url,
      after: url
    });
  }

  cache(url, path) {
    this.push({
      type: "cache",
      url: url,
      path: path
    });
  }

  remote(url, hosts) {
    url = /^https?:\/\//.test(url) ? url : "http:" + url;
    var host = urlLib.parse(url).host;
    if (typeof hosts == "object") {
      host = hosts[host] ? hosts[host] : host;
    }
    else if (typeof hosts == "string") {
      host = hosts;
    }
    this.push({
      type: "remote",
      url: url,
      host: host
    });
  }
}

module.exports = Stack;
