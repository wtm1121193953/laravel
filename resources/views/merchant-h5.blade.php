<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>后台管理</title>
    <link rel="stylesheet" href="{{mix('css/all.css')}}">

</head>
<body>
<div id="app">

</div>

<script>
    var captcha_url = '{{ captcha_src() }}';
</script>
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/merchant-h5.js') }}"></script>
</body>
</html>
