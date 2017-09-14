var chalk = require("chalk");
var fs = require("fs");
var path = require("path");
var util = require("util");

var Log = {};
var dir = fs.readdirSync(__dirname + "/lib");
dir.forEach(function (i) {
  var name = path.basename(i, ".js");
  Log[name] = require("./lib/" + name);
});

exports = module.exports = function (data, just) {
  data.forEach(function (item) {
    var type = item.type;
    if (type && typeof Log[type] == "function" && (
      typeof just == "undefined" ||
      (typeof just == "string" && just == type) ||
      (util.isArray(just) && just.indexOf(type) != -1)
      )) {
      Log[type](item);
    }
  });
};

exports.chalk = chalk;
exports.stack = require("./stack");
