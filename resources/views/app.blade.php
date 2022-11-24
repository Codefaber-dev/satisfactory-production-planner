<!DOCTYPE html>
<html class="bg-gray-100 dark:bg-slate-900" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="guest-token" content="{{ guest_token() }}">

        <!-- SEO -->
        <x-seo::meta />

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <!-- Scripts -->
        @routes
        <script src="{{ mix('js/app.js') }}" defer></script>

        @production
        <script defer data-domain="satisfactoryproductionplanner.com" src="https://plausible.io/js/plausible.js"></script>


        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-TEFR2C3YV2"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'G-TEFR2C3YV2');
        </script>

        <!-- adsense -->
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9180042246032056" crossorigin="anonymous"></script>
        @endproduction
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-slate-900" style="background-attachment: fixed">
        @inertia

        @env ('local')
            <script src="https://satis-pp.test/browser-sync/browser-sync-client.js"></script>
        @endenv
    </body>
</html>
