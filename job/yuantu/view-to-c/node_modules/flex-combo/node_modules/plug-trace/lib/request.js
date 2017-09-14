var chalk = require("chalk");

exports = module.exports = function (item) {
  console.log("%s => %s %s  %s",
    chalk.bgBlue.black(" Request "),
    chalk.underline.blue(item.host),
    chalk.blue(typeof item.list == "object" ? JSON.stringify(item.list, null, 2) : item.list),
    chalk.black(item.time)
  );
};
