<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>React测试</title>
    <link rel="stylesheet" href="{{ mix('css/bootstrap-animation.css') }}">
    <style>
        body{
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<style>
    body {
        background-color: #f9f9f9;
    }
</style>
<body>
{{-- 初始页面加载动画 --}}
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var loader = document.getElementById('bootstrap-loader');
        loader.parentNode.removeChild(loader);
    });
</script>

<div id="app"></div>

<script src="{{ mix('react-antd/manifest.js') }}"></script>
<script src="{{ mix('react-antd/vendor.js') }}"></script>
<script src="{{ mix('react-antd/index.js') }}"></script>
</body>
</html>