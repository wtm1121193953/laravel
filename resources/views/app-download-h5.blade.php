<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>大千生活</title>
    <style>
        @-webkit-keyframes scale {
            0% {
                -webkit-transform: scale(1);
                transform: scale(1);
                opacity: 1; }

            45% {
                -webkit-transform: scale(0.1);
                transform: scale(0.1);
                opacity: 0.7; }

            80% {
                -webkit-transform: scale(1);
                transform: scale(1);
                opacity: 1; } }
        @keyframes scale {
            0% {
                -webkit-transform: scale(1);
                transform: scale(1);
                opacity: 1; }

            45% {
                -webkit-transform: scale(0.1);
                transform: scale(0.1);
                opacity: 0.7; }

            80% {
                -webkit-transform: scale(1);
                transform: scale(1);
                opacity: 1; } }

        .ball-pulse > div:nth-child(0) {
            -webkit-animation: scale 0.75s 0s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: scale 0.75s 0s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .ball-pulse > div:nth-child(1) {
            -webkit-animation: scale 0.75s 0.12s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: scale 0.75s 0.12s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .ball-pulse > div:nth-child(2) {
            -webkit-animation: scale 0.75s 0.24s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: scale 0.75s 0.24s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .ball-pulse > div:nth-child(3) {
            -webkit-animation: scale 0.75s 0.36s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: scale 0.75s 0.36s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .ball-pulse > div {
            background-color: #fff;
            width: 15px;
            height: 15px;
            border-radius: 100%;
            margin: 2px;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
            display: inline-block; }

        @-webkit-keyframes ball-spin-fade-loader {
            50% {
                opacity: 0.3;
                -webkit-transform: scale(0.4);
                transform: scale(0.4); }

            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1); } }

        @keyframes ball-spin-fade-loader {
            50% {
                opacity: 0.3;
                -webkit-transform: scale(0.4);
                transform: scale(0.4); }

            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1); } }

        .ball-spin-fade-loader {
            position: relative; }
        .ball-spin-fade-loader > div:nth-child(1) {
            top: 25px;
            left: 0;
            -webkit-animation: ball-spin-fade-loader 1s 0s infinite linear;
            animation: ball-spin-fade-loader 1s 0s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(2) {
            top: 17.04545px;
            left: 17.04545px;
            -webkit-animation: ball-spin-fade-loader 1s 0.12s infinite linear;
            animation: ball-spin-fade-loader 1s 0.12s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(3) {
            top: 0;
            left: 25px;
            -webkit-animation: ball-spin-fade-loader 1s 0.24s infinite linear;
            animation: ball-spin-fade-loader 1s 0.24s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(4) {
            top: -17.04545px;
            left: 17.04545px;
            -webkit-animation: ball-spin-fade-loader 1s 0.36s infinite linear;
            animation: ball-spin-fade-loader 1s 0.36s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(5) {
            top: -25px;
            left: 0;
            -webkit-animation: ball-spin-fade-loader 1s 0.48s infinite linear;
            animation: ball-spin-fade-loader 1s 0.48s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(6) {
            top: -17.04545px;
            left: -17.04545px;
            -webkit-animation: ball-spin-fade-loader 1s 0.6s infinite linear;
            animation: ball-spin-fade-loader 1s 0.6s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(7) {
            top: 0;
            left: -25px;
            -webkit-animation: ball-spin-fade-loader 1s 0.72s infinite linear;
            animation: ball-spin-fade-loader 1s 0.72s infinite linear; }
        .ball-spin-fade-loader > div:nth-child(8) {
            top: 17.04545px;
            left: -17.04545px;
            -webkit-animation: ball-spin-fade-loader 1s 0.84s infinite linear;
            animation: ball-spin-fade-loader 1s 0.84s infinite linear; }
        .ball-spin-fade-loader > div {
            background-color: #fff;
            width: 15px;
            height: 15px;
            border-radius: 100%;
            margin: 2px;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
            position: absolute; }

        /**
         * Lines
         */
        @-webkit-keyframes line-scale {
            0% {
                -webkit-transform: scaley(1);
                transform: scaley(1); }

            50% {
                -webkit-transform: scaley(0.4);
                transform: scaley(0.4); }

            100% {
                -webkit-transform: scaley(1);
                transform: scaley(1); } }
        @keyframes line-scale {
            0% {
                -webkit-transform: scaley(1);
                transform: scaley(1); }

            50% {
                -webkit-transform: scaley(0.4);
                transform: scaley(0.4); }

            100% {
                -webkit-transform: scaley(1);
                transform: scaley(1); } }

        .line-scale > div:nth-child(1) {
            -webkit-animation: line-scale 1s 0.1s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: line-scale 1s 0.1s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .line-scale > div:nth-child(2) {
            -webkit-animation: line-scale 1s 0.2s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: line-scale 1s 0.2s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .line-scale > div:nth-child(3) {
            -webkit-animation: line-scale 1s 0.3s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: line-scale 1s 0.3s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .line-scale > div:nth-child(4) {
            -webkit-animation: line-scale 1s 0.4s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: line-scale 1s 0.4s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .line-scale > div:nth-child(5) {
            -webkit-animation: line-scale 1s 0.5s infinite cubic-bezier(.2, .68, .18, 1.08);
            animation: line-scale 1s 0.5s infinite cubic-bezier(.2, .68, .18, 1.08); }
        .line-scale > div {
            background-color: rgb(105, 187, 255);
            width: 4px;
            height: 40px;
            border-radius: 2px;
            margin: 2px;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
            display: inline-block;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #bootstrap-loader {
            width: 100%;
            height: 100%;
            text-align: center;
        }
        #bootstrap-loader .loader {
            margin: auto;
            padding-top: 300px;
        }
    </style>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        #app {
            max-width: 750px;
            margin: 0 auto;
        }

        #app .header {
            padding: 1.11rem 0 0;
        }

        #app .header img {
            display: block;
            margin: 0 auto;
            width: 2.01rem;
            height: 2.01rem;
        }

        #app .header p {
            margin-top: 0.6rem;
            font-size: 0.46rem;
            color: #343434;
            text-align: center;
        }

        #app .version p {
            margin-top: 0.28rem;
            text-align: center;
            font-size: 0.28rem;
            line-height: 0.34rem;
            color: #8b8b8b;
        }

        #app .version p span {
            display: inline-block;
            width: 0.2rem;
        }

        #app .qrcode {
            margin: 0.6rem 0 0;
        }

        #app .qrcode img {
            display: block;
            margin: 0 auto;
            width: 2.18rem;
            height: 2.18rem;
            border: 0.02rem solid #8b8b8b;
        }

        #app .handler {
            margin-top: 0.4rem;
            padding: 0 0.4rem;
            text-align: center;
        }

        #app .handler .btn {
            background: #2acbac;
            color: #fff;
            text-align: center;
            font-size: 0.3rem;
            height: 0.92rem;
            line-height: 0.92rem;
            border-radius: 0.1rem;
            width: 3.2rem;
            display: inline-block;
        }

        #app .handler .btn:before {
            content: '';
            display: inline-block;
            vertical-align: middle;
            margin-right: 0.2rem;
            margin-top: -0.08rem;
        }

        #app .handler #iphone:before {
            width: 0.4rem;
            height: 0.5rem;
            background: url({{ asset('static/img/icon_iphone.png') }}) no-repeat center;
            background-size: 100% 100%;
            -webkit-background-size: 100% 100%;
            -moz-background-size: 100% 100%;
        }

        #app .handler #android:before {
            width: 0.42rem;
            height: 0.5rem;
            background: url({{ asset('static/img/icon_android.png') }}) no-repeat center;
            background-size: 100% 100%;
            -webkit-background-size: 100% 100%;
            -moz-background-size: 100% 100%;
        }

        #app .tips {
            padding: 0.5rem 0;
            font-size: 0.28rem;
            color: #8b8b8b;
            text-align: center;
        }

        #app .null {
            font-size: 0.34rem;
            color: #333;
            text-align: center;
        }
    </style>
    <script>
        (function(doc, win) {
            var docEl = doc.documentElement,
                isIOS = navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
                dpr = isIOS ? Math.min(win.devicePixelRatio, 3) : 1,
                dpr = window.top === window.self ? dpr : 1, //被iframe引用时，禁止缩放
                dpr = 1,
                scale = 1 / dpr,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize';
            docEl.dataset.dpr = dpr;
            var metaEl = doc.createElement('meta');
            metaEl.name = 'viewport';
            metaEl.content = 'initial-scale=' + scale + ',maximum-scale=' + scale + ', minimum-scale=' + scale;
            docEl.firstElementChild.appendChild(metaEl);
            var recalc = function() {
                var width = docEl.clientWidth;
                if (width / dpr > 750) {
                    width = 750 * dpr;
                }
                // 乘以100，px : rem = 100 : 1
                docEl.style.fontSize = 100 * (width / 750) + 'px';
            };
            recalc()
            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
        })(document, window);
    </script>
</head>
<body>
<!--初始页面加载动画-->
<div id="bootstrap-loader">
    <div class="loader">
        <div class="line-scale">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>

<div id="app">
    <div class="header">
        <img src="{{ asset('static/img/app-logo.png') }}" />
        <p>大千生活</p>
    </div>

    {{-- ios --}}
    <?php if ($ios != null){ ?>
        <div id="ios-box">
            <div class="version">
                <p>版本：{{!empty($ios->version_no) ? $ios->version_no : ""}}<span></span>大小：{{!empty($ios->app_size) ? $ios->app_size : ""}}MB</p>
                <p>更新时间：{{!empty($ios->update_date) ? $ios->update_date : ""}}<span></span>{{!empty($ios->update_time) ? $ios->update_time : ""}}</p>
            </div>
            <div class="qrcode">
                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->errorCorrection('H')->encoding('UTF-8')->margin(3)->size(375)->generate(url()->full())) !!}" />
            </div>
            <div class="handler">
                <div id="iphone" class="btn" package-url="{{$ios->package_url}}">iPhone版下载</div>
            </div>
        </div>
    <?php } ?>

    {{-- 安卓 --}}
    <?php if ($android != null){ ?>
        <div id="andriod-box">
            <div class="version">
                <p>版本：{{!empty($android->version_no) ? $android->version_no : ""}}<span></span>大小：{{!empty($android->app_size) ? $android->app_size : ""}}MB</p>
                <p>更新时间：{{!empty($android->update_date) ? $android->update_date : ""}}<span></span>{{!empty($android->update_time) ? $android->update_time : ""}}</p>
            </div>
            <div class="qrcode">
                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->errorCorrection('H')->encoding('UTF-8')->margin(3)->size(375)->generate(url()->full())) !!}" />
            </div>
            <div class="handler">
                <div id="android" class="btn" package-url="{{$android->package_url}}">Android版下载</div>
            </div>
        </div>
    <?php } ?>
    
    <div id="tips" class="tips">或者用手机扫描二维码安装</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var loader = document.getElementById('bootstrap-loader');
        loader.parentNode.removeChild(loader);
    });

    //返回true表示为手机端打开，返回false表示为pc端打开  
    function checkClient() {
        var userAgentInfo = navigator.userAgent;
        var Agents = new Array("Android","iPhone","SymbianOS","Windows Phone","iPad","iPod");
        var flag = false;

        for(var v = 0; v < Agents.length; v++) {
            if(userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = Agents[v];
                break;
            }
        }

        return flag;
    }
    
    //跳转地址
    var lock = false;
    var parentBox = document.getElementById('app'),
        ios_box = document.getElementById('ios-box'),
        iosEl = document.getElementById('iphone'),
        android_box = document.getElementById('andriod-box'),
        androidEl = document.getElementById('android'),
        tips = document.getElementById('tips'),
        clientType = checkClient()

    if(/(iPhone|iPad|iPod)/.test(clientType)) {
        //苹果
        //删除
        parentBox.removeChild(android_box)

        if({{isset($ios) && !empty($ios)}}) {
            //链接
            var url = iosEl.getAttribute('package-url'),
                action = null

            //http:/https: => itms-apps:
            url = url.replace(/(http\:|https\:)/g, "itms-apps:")
            
            if(url) {
                window.location.href = url
                action = function() {
                    window.location.href = url
                }
            } else {
                action = function() {
                    alert('下载异常，请重新刷新页面后继续下载')
                }
            }

            //监听
            iosEl.addEventListener('click', function() {
                if(lock) return
                lock = true

                action()
                lock = false
            })
        } else {
            parentBox.removeChild(ios_box)
            parentBox.removeChild(tips)
            createNull(parentBox)
        }
    } else {
        //安卓
        //删除
        parentBox.removeChild(ios_box)

        if({{isset($android) && !empty($android)}}) {
            //链接
            var url = androidEl.getAttribute('package-url'),
                action = null

            if(url) {
                var version = "{{!empty($android->app_num) ? $android->app_num : 'v0.0.1'}}"
                var filename = 'daqian-' + version + '.apk'

                downloadFile(filename, url)
                action = function() {
                    downloadFile(filename, url)
                }
            } else {
                action = function() {
                    alert('下载异常，请重新刷新页面后继续下载')
                }
            }

            //监听
            androidEl.addEventListener('click', function() {
                if(lock) return
                lock = true

                action()
                lock = false
            })
        } else {
            parentBox.removeChild(android_box)
            parentBox.removeChild(tips)
            createNull(parentBox)
        }
    }

    /**
     * 通过创建a标签点击下载安装包
     * 
     * @param {String} filename 自定义文件名（有些浏览器不支持）
     * @param {String} content  下载地址
     */
    function downloadFile(filename, content) {
        var a = document.createElement('a')
        a.href = content;
        a.target = '_blank';
        a.download = filename;
        a.click();
    }

    /**
     * 创建暂无数据标签
     */
    function createNull(parentBox) {
        var nullEl = document.createElement('div')
        nullEl.className = 'null'
        nullEl.appendChild(document.createTextNode('暂无数据，'))
        nullEl.appendChild(document.createElement('br'))
        nullEl.appendChild(document.createTextNode('可自行前往应用商店搜索“大千生活”下载'))
        parentBox.appendChild(nullEl)
    }

</script>
</body>
</html>
