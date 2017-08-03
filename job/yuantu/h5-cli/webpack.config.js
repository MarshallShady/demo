var webpack = require('webpack')
var HtmlWebpackPlugin = require('html-webpack-plugin')
var BPTDL = require('babel-plugin-transform-decorators-legacy')

module.exports = {
	entry: {
		app: './src/app.js'
		,vendor: ['./src/base/util.js']
	}
	,output: {
		path: __dirname + '/build'
		,filename: '[name].bundle.js'
		,chunkFilename:'[name].bundle.js'
	}
	,module: {
		loaders: [
			{
				test: /\.js$/
				,exclude: /node_module/
				,loader: 'babel-loader'
				,query: {
					presets: ['es2017', 'react', 'stage-0'],
					plugins: ['transform-decorators-legacy']
				}
			}
		]
	}
	,plugins: [
		new HtmlWebpackPlugin({
			template: './src/index.html'
			,inject: 'body'
		})
		,new webpack.optimize.CommonsChunkPlugin({
			name: 'vendor', // 这公共代码的 chunk 名为 'commons'
			filename: '[name].bundle.js', // 生成后的文件名 commons.bundle.js 
			minChunks: 2 // 设定要有 n 个 chunk（即n个页面）加载的 js 模块才会被纳入公共代码
		})
	]
}