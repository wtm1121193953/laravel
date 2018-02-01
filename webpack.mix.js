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

mix
    // 加载 boss 前端模块
    .js('resources/assets/boss/js/app.js', 'public/js/boss.js')
    // 抽离不会变的js模块
    .extract(['axios', 'js-cookie', 'lockr', 'lodash', 'nprogress', 'vue', 'vue-router', 'element-ui', 'font-awesome-webpack', 'vue-echarts-v3', 'vue-quill-editor'])
    .version()
    // 加载通用样式
    .styles(['node_modules/element-ui/lib/theme-default/index.css',
        'resources/assets/css/global.css'
    ], 'public/css/all.css')
    // 复制element-ui的字体文件
    .copy('node_modules/element-ui/lib/theme-default/fonts', 'public/css/fonts/');

// mix.react('resources/assets/react/src/index.js', 'public/element-react/index.js');
// mix.extract(['react', 'react-dom', 'element-react', 'element-theme-default']);

// mix.react('resources/assets/react-antd/src/index.js', 'public/react-antd/index.js');
// mix.extract(['react', 'react-dom', 'antd']);


// 页面初始动画
mix.styles('resources/assets/css/bootstrap-animation.css', 'public/css/bootstrap-animation.css');

// 如果是运行 npm run prod 命令, 启用版本号
mix.version();
if (mix.config.inProduction) {
}