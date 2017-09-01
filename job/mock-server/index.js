const Koa = require('koa')
const path = require('path')

const Mock = require('./mock')

const app = new Koa()

const LOCAlHOST_HREF = 'http://localhost:3000/'

app.use( async ( ctx ) => {
  // console.log( ctx.req )
  const mockJson = Mock.parseModule( ctx.url )
  if ( mockJson ) {
    let repJson = Mock.parseMock( mockJson )
    ctx.body = Mock.repJsonp( ctx.url, repJson )
    return 
  }
  ctx.body = '<h1 style="text-align: center;line-height: 2;font-size: 100px;">404</h1>'
})

app.listen(3000, () => {
  console.log( 'mock url:'+ LOCAlHOST_HREF )
})