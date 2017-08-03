webpackJsonp([2],{

/***/ 86:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (immutable) */ __webpack_exports__["default"] = pages;

/**
 * 进行模块的注册
 * 
 * @export
 * @returns 返回一个 Promise 用于 await 语法，实际返回 注册成功的模块
 */
function pages() {
  return new Promise(function (resolve, reject) {
    let pageName = UtilQuery().pageName;
    pageName == 'a.js' && __webpack_require__.e/* require.ensure */(5).then((function () {
      const a = __webpack_require__(191);
      resolve(a);
    }).bind(null, __webpack_require__)).catch(__webpack_require__.oe);

    pageName == 'b.js' && __webpack_require__.e/* require.ensure */(4).then((function () {
      const b = __webpack_require__(192);
      resolve(b);
    }).bind(null, __webpack_require__)).catch(__webpack_require__.oe);
  });
}

function UtilQuery() {
  var search = window.location.search,
      query = {};
  search = search.slice(1).split('&');
  search.forEach(function (val) {
    var temp = val.split('=');
    query[temp[0]] = decodeURIComponent(temp[1]);
  });
  return query;
}

// import a from './a'
// console.log( 'base req a', a)
// export default {
//   name: 'base'
// }

/***/ })

});