var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("%s <= %s  %s\n",
    chalk.bgRed.white(" Failure "),
    chalk.red(item.url),
    chalk.black(item.time)
  );
};
