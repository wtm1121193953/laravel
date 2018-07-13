let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// 禁用imgLoader, 只对图片做简单的复制
mix.options({
    imgLoaderOptions: {
        enabled: false,
    }
});

mix.webpackConfig(webpack => {
    return {
        plugins: [
            new webpack.ProvidePlugin({
                'window.Quill': 'quill/dist/quill.js'
            })
        ],
        module: {
            rules: [
                // 覆盖laravel-mix中的图片处理, 编译的图片增加原有的路径, 以防止不同目录同名图片的相互覆盖
                {
                    // only include svg that doesn't have font in the path or file name by using negative lookahead
                    test: /(\.(png|jpe?g|gif)$|^((?!font).)*\.svg$)/,
                    loader: 'file-loader',
                    options: {
                        name: path => {
                            if (!/node_modules|bower_components/.test(path)) {
                                return (
                                    Config.fileLoaderDirs.images + '/' +
                                    path
                                        .replace(/\\/g, '/')
                                        .replace(
                                            /(.*(images|image|img|assets))\//g,
                                            ''
                                        ) +
                                    '?[hash]'
                                );
                            }

                            return (
                                Config.fileLoaderDirs.images +
                                '/vendor/' +
                                path
                                    .replace(/\\/g, '/')
                                    .replace(
                                        /((.*(node_modules|bower_components))|images|image|img|assets)\//g,
                                        ''
                                    ) +
                                '?[hash]'
                            );
                        },
                        publicPath: Config.resourceRoot
                    }
                },
                //单独转换：例外模块没有经过编译，造成ie浏览器不兼容
                {
                    test: /\.jsx?$/,
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            "es2015"
                        ]
                    },
                    include: [
                        path.resolve('node_modules/element-ui/src/mixins/emitter'),
                        path.resolve('node_modules/quill-image-extend-module/index'),
                        path.resolve('node_modules/ImageExtend'),
                    ]
                }
            ]
        }
    };
})

// 加载 admin 前端模块
mix.js('./resources/admin/app.js', 'public/js/admin.js');
mix.js('./resources/oper/app.js', 'public/js/oper.js');
mix.js('./resources/merchant/app.js', 'public/js/merchant.js');
mix.js('./resources/merchant-h5/app.js', 'public/js/merchant-h5.js');

// 抽离不会变的js模块
mix.extract([
    '@tweenjs/tween.js',
    'axios',
    'js-cookie',
    'lockr',
    'lodash',
    'moment',
    'nprogress',
    'vue',
    'vue-router',
    'element-ui',
    'vue-amap',
    // 'vue-echarts-v3',
    // 'vue-quill-editor'
]);

// 加载通用样式
mix.styles(['node_modules/element-ui/lib/theme-chalk/index.css',
    'node_modules/nprogress/nprogress.css',
    'resources/assets/css/base.css',
    'resources/assets/css/global.css'
], 'public/css/all.css')
// 复制element-ui的字体文件
mix.copy('node_modules/element-ui/lib/theme-chalk/fonts', 'public/css/fonts/');

// 如果是运行 npm run prod 命令, 启用版本号
mix.version();
if (mix.config.inProduction) {

}