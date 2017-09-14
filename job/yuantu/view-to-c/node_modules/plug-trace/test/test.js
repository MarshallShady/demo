var trace = require("../lib/request");
trace({
  time:"20:00:00",
  host:"http://www.taobao.com",
  list: [
    "a.js",
    "b.js"
  ]
});

trace = require("../lib/response");
trace({
  time:"20:00:00",
  url:"http://www.taobao.com"
});

trace = require("../lib/error");
trace({
  time:"20:00:00",
  msg:"http://www.taobao.com Not Found!",
  tip:"IO"
});

trace = require("../lib/warn");
trace({
  time:"20:00:00",
  msg:"http://www.taobao.com Not Found!",
  tip:"IO"
});

trace = require("../lib/engine");
trace({
  time:"20:00:00",
  url:"/a/b",
  path:"/src/aaa/a/b"
});

trace = require("../lib/local");
trace({
  time:"20:00:00",
  url:"/a/b",
  path:"/src/aaa/a/b"
});

trace = require("../lib/cache");
trace({
  time:"20:00:00",
  url:"/a/b",
  path:"/src/aaa/a/b"
});

trace = require("../lib/remote");
trace({
  time:"20:00:00",
  url:"/a/b",
  "reqopt": {
    "protocol": "http:",
    "host": "1.1.1.1",
    "path": "/a/a/a/a.js",
    "headers": {
      "hostname": "g.tbcdn.cn"
    }
  }
});