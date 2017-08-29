const webpack = require('webpack');
let mix = require('laravel-mix');
global.Mix = new (require('./node_modules/laravel-mix/src/Mix'))();

let ManifestPlugin = require('./node_modules/laravel-mix/src/plugins/ManifestPlugin');
let WebpackChunkHashPlugin = require('webpack-chunk-hash');

module.exports = {
    entry: {
        src: './resources/assets/react/src/index.js'
    },
    output: {
        path: '/public/element-react',
        publicPath: '/public/element-react',
        // chunkFilename: '[chunkhash:12].js',
        filename: '[chunkhash:12].js'
    },
    plugins: [
        new webpack.DefinePlugin({ 'process.env.NODE_ENV': JSON.stringify('production') }),
        new webpack.optimize.UglifyJsPlugin({
            output: {
                comments: false
            }
        }),
        new ManifestPlugin(),
        // new HashedModuleIdsPlugin(),
        new WebpackChunkHashPlugin()
    ],
    resolve: {
        extensions: ['.js', '.jsx']
    },
    module: {
        loaders: [
            {
                test: /\.jsx?$/,
                loader: 'babel-loader',
                query: {
                    presets: ['es2015', "stage-1", "react"]
                }
            },
            {
                test: /\.css$/,
                loaders: ['style-loader', 'css-loader']
            },
            {
                test: /\.(ttf|eot|svg|woff|woff2)(\?.+)?$/,
                loader: 'file-loader?name=[hash:12].[ext]'
            }
        ]
    }
}