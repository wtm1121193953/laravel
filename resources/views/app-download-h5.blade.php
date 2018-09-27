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
            margin-top: 0.8rem;
            padding: 0 0.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        #app .handler .btn {
            background: #2acbac;
            color: #fff;
            text-align: center;
            font-size: 0.3rem;
            height: 0.92rem;
            line-height: 0.92rem;
            border-radius: 0.1rem;
            flex: 1;
            margin-left: 0.3rem;
        }

        #app .handler .btn::before {
            content: '';
            display: inline-block;
            vertical-align: middle;
            width: 0.4rem;
            height: 0.5rem;
            margin-right: 0.2rem;
        }

        #app .handler .btn:first-child {
            margin-left: 0;
        }

        #app .handler .btn:first-child::before {
            background: url({{ asset('static/img/icon_iphone.png') }}) no-repeat center;
            background-size: 100% 100%;
            -webkit-background-size: 100% 100%;
            -moz-background-size: 100% 100%;
        }

        #app .handler .btn:last-child::before {
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
    <div class="version">
        <p>版本：{{$ios->app_num}}<span></span>大小：40.3MB</p>
        <p>更新时间：2018-09-26<span></span>12:23:06</p>
    </div>
    <div class="qrcode">
        <img src="https://xiaochengxu.niucha.ren/storage/miniprogram/app_code/_688_375.jpg" />
    </div>
    <div class="handler">
        <div id="iphone" class="btn">iPhone版下载</div>
        <div id="android" class="btn">Android版下载</div>
    </div>
    <div class="tips">或者用手机扫描二维码安装</div>
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
    var openApp = '',
        downloadIphone = '',
        downloadAndroid = '';
    var lock = false;

    switch (checkClient()) {
        case "Android":
            //安卓
            console.log('android')
            break;
        case "iPhone":case "iPad":case "iPod":
            //苹果
            console.log('iphone')
            break;
    }
    
    document.getElementById('android').addEventListener('click', function() {
        if(lock) return;
        lock = true;

        console.log('android')
    })

    document.getElementById('iphone').addEventListener('click', function() {
        if(lock) return;
        lock = true;

        console.log('iphone')
    })
</script>
</body>
</html>
