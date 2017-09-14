var chalk = require("chalk");

function isRegExp(obj) {
  return Object.prototype.toString.call(obj) === "[object RegExp]";
}

exports = module.exports = function (item) {
  var msg = typeof item.msg == "object" ? JSON.stringify(item.msg, function (key, value) {
    if (isRegExp(value) || typeof value === "function") {
      return "$$$" + value.toString() + "$$$";
    }
    else {
      return value;
    }
    return (typeof value === "function") ? "[Function]" : value;
  }, 2) : item.msg;
  msg = msg.replace(/^/mg, "    ");
  msg = msg.replace(/"?\$\$\$"?/g, '');

  console.log("    %s%s \n%s  %s",
    chalk.bgCyan.gray(" Info "),
    item.tip ? chalk.bgBlack(' ' + item.tip + ' ') : '',
    chalk.white(msg),
    chalk.black(item.time)
  );
};
