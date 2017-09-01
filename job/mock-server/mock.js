const { URL } = require('url')
const Mock = require('mockjs')

const config = require('./mock-config')

// console.log( config )
module.exports = {
  parseModule: function ( href ) {
    const url = new URL( 'http://localhost:3000'+ href )
    return config[ url.pathname ]
  }
  ,parseMock: function ( mockJson ) {
    return Mock.mock( mockJson )
  }
  ,repJsonp: function ( href, repJson ) {
    console.log( href )
    const url = new URL( 'http://localhost:3000'+ href )
    const jsonpCallback = url.searchParams.get('callback')
    // console.log( url )
    const strJson = JSON.stringify( repJson )
    if ( jsonpCallback ) {
      return jsonpCallback + '(' + strJson + ')'
    }
    return strJson
  }
}
