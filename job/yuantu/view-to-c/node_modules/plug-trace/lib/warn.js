var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s%s %s  %s",
    chalk.bgYellow.gray(" Warn "),
    item.tip ? chalk.bgBlack(' ' + item.tip + ' ') : '',
    item.msg,
    chalk.black(item.time)
  );
};
