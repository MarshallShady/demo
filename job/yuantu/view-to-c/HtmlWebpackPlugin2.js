var HtmlWebpackPlugin = require("html-webpack-plugin")

//扩展 HtmlWebpackPlugin 在插入js css之前可以自定义添加资源到html中
//injectAssetsIntoAssetTags

function HtmlWebpackPlugin2(options){
  this.variable = options.variable;
  HtmlWebpackPlugin.apply(this,arguments);
}

HtmlWebpackPlugin2.prototype = new HtmlWebpackPlugin();

HtmlWebpackPlugin2.prototype.injectAssetsIntoAssetTags = function(assetTags){
  return assetTags;
};

HtmlWebpackPlugin2.prototype.injectAssetsIntoHtml = function (html, assets, assetTags) {

  //加上我们定义的vendor
  // assetTags.body = addAssetTags.slice(0).concat(assetTags.body);

  assetTags = this.injectAssetsIntoAssetTags( assetTags );
  //cnd_domain
  if(this.variable){
    for(var key in this.variable){
      html = html.replace( new RegExp("{"+key+"}","ig"), this.variable[key])
    }
  }

  var htmlRegExp = /(<html[^>]*>)/i;
  var headRegExp = /(<\/head>)/i;
  var bodyRegExp = /(<\/body>)/i;
  var body = assetTags.body.map(this.createHtmlTag);
  var head = assetTags.head.map(this.createHtmlTag);

  if (body.length) {
    if (bodyRegExp.test(html)) {
      // Append assets to body element
      html = html.replace(bodyRegExp, function (match) {
        return body.join('') + match;
      });
    } else {
      // Append scripts to the end of the file if no <body> element exists:
      html += body.join('');
    }
  }

  if (head.length) {
    // Create a head tag if none exists
    if (!headRegExp.test(html)) {
      if (!htmlRegExp.test(html)) {
        html = '<head></head>' + html;
      } else {
        html = html.replace(htmlRegExp, function (match) {
          return match + '<head></head>';
        });
      }
    }

    // Append assets to head element
    html = html.replace(headRegExp, function (match) {
      return head.join('') + match;
    });
  }

  // Inject manifest into the opening html tag
  if (assets.manifest) {
    html = html.replace(/(<html[^>]*)(>)/i, function (match, start, end) {
      // Append the manifest only if no manifest was specified
      if (/\smanifest\s*=/.test(match)) {
        return match;
      }
      return start + ' manifest="' + assets.manifest + '"' + end;
    });
  }
  return html;
};

module.exports = HtmlWebpackPlugin2;