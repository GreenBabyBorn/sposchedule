<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon32.ico">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/favicon180.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="author" content="greenbabyborn">
    <title>Пары РКЭ</title>
    <meta name="description" content="Расписание учебного процесса Рязанского Колледжа Электроники">
    <meta name="keywords" content="Расписание РКЭ, Пары РКЭ, РКЭ, Звонки РКЭ, Рязанский колледж электроники, расписание на завтра РКЭ, обучающимся РКЭ">
    @vite(['resources/js/main.ts'])
    @if (!env('APP_DEBUG'))
    <meta name="yandex-verification" content="ea9244c5d083efb1" />
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m, e, t, r, i, k, a) {
            m[i] = m[i] || function() {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(98521656, "init", {
            clickmap: true
            , trackLinks: true
            , accurateTrackBounce: true
            , webvisor: true
        });

    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/98521656" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
    @endif
</head>

<body>
    <div id="app"></div>
</body>

</html>
