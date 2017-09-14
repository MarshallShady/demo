var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s %s (%s)  %s",
    chalk.bgWhite.black(" Local "),
    chalk.white(item.path),
    item.url,
    chalk.black(item.time)
  );
};
