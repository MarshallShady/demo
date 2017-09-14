var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s %s (%s)  %s",
    chalk.inverse(" Cache "),
    item.path,
    item.url,
    chalk.black(item.time)
  );
};
