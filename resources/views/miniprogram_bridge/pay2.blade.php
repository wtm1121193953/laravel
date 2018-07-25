<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>支付跳转</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .title {
            font-weight: 600;
            line-height: 100px;
            margin-top: 30px;
            text-align: center;
        }
        .image {
            width: 70%;
            margin-left: 10%;
            border: 1px #ccc solid;
            padding: 5%;
        }
        .error {
            text-align: center;
            margin-top: 30px;
            color: #e34c37;
        }
    </style>
</head>
<body>
    pay2  hahahahahha h
    <button onclick="goback()">跳回去</button>
    <script>
        var goback = function(){
            location.go(-1)
        }
    </script>
</body>
</html>