var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s%s %s  %s",
    chalk.bgRed.white(" Error "),
    item.tip ? chalk.bgWhite.red(' ' + item.tip + ' ') : '',
    chalk.red(item.msg),
    chalk.black(item.time)
  );
};
