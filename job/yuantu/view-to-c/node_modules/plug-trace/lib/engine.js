var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("    %s %s (%s)  %s",
    chalk.bgCyan.black(" Engine "),
    chalk.cyan(item.path),
    item.url,
    chalk.black(item.time)
  );
};
