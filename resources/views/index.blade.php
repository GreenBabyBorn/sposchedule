<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="favicon.png" />
    {{-- <title>Расписание</title> --}}
    <meta name="description" content="Расписание">
    @vite(['resources/js/main.ts'])
</head>

<body>
    <div id="app"></div>
</body>

</html>
