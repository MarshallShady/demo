var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s %s (%s)  %s",
    chalk.bgYellow.gray(" Remote "),
    chalk.yellow(item.url),
    item.host || '',
    chalk.black(item.time)
  );
};
