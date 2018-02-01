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

// 加载 admin 前端模块
mix.js('./resources/admin/app.js', 'public/js/admin.js');

// 抽离不会变的js模块
mix.extract([
    'axios',
    'js-cookie',
    'jquery',
    'lockr',
    'lodash',
    'moment',
    'nprogress',
    'vue',
    'vue-router',
    'element-ui',
    // 'font-awesome-webpack',
    // 'vue-echarts-v3',
    // 'vue-quill-editor'
]);

// 加载通用样式
mix.styles(['node_modules/element-ui/lib/theme-chalk/index.css',
    'resources/assets/css/base.css', 'resources/assets/css/global.css'
], 'public/css/all.css')
// 复制element-ui的字体文件
mix.copy('node_modules/element-ui/lib/theme-chalk/fonts', 'public/css/fonts/');

// 如果是运行 npm run prod 命令, 启用版本号
mix.version();
if (mix.config.inProduction) {

}