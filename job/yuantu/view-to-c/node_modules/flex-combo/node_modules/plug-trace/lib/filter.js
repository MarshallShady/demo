var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s %s: %s (%s)  %s",
    chalk.magenta("[Filter]"),
    chalk.magenta(item.regx),
    chalk.white(item.after),
    item.before,
    chalk.black(item.time)
  );
};
