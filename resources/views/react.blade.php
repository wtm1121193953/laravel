<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>React测试</title>
</head>
<body>

<div id="app"></div>

<script src="/public/element-react"></script>
<script type="text/babel">
    import React from 'react';
    import ReactDOM from 'react-dom';
    import {Button } from 'element-react';

    import 'element-theme-default';

    ReactDOM.render(
    <Button type="primary">Hello </Button>, document.getElementById('app')
    );
</script>
</body>
</html>