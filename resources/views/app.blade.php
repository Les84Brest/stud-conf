<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Платформа конференций</title>
    @viteReactRefresh
    @vite(['resources/js/app.tsx', 'resources/css/app.css',])
</head>
<body>
    <div id="app"></div>
</body>
</html>