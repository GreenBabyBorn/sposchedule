<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="yandex-verification" content="ea9244c5d083efb1" />
    <meta name="author" content="greenbabyborn">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/png" href="favicon.png" />
    <title>Пары РКЭ</title>
    <meta name="description" content="Пары РКЭ - оффициальное расписание занятий Рязанского Колледжа Электроники">
    @vite(['resources/js/main.ts'])

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
        });

    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/98521656" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
</head>

<body>
    <div id="app"></div>

</body>

</html>
