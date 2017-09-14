var chalk = require("chalk");
var fs = require("fs");

exports = module.exports = function (item) {
  console.log("%s <= %s  %s\n",
    chalk.bgGreen.black(" Response "),
    chalk.green(item.url),
    chalk.black(item.time)
  );

  if (item.content) {
    var p = process.cwd() + "/.cache";
    if (!fs.existsSync(p)) {
      fs.mkdirSync(p);
      fs.chmod(p, 0777);
    }
  }
};
