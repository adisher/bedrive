@php use Sentry\Laravel\Integration; @endphp
    <!doctype html>
<html lang="{{$bootstrapData->get('language')}}">
<head>
    <base href="{{ $htmlBaseUri }}">

    @if(isset($meta))
        @include('common::prerender.meta-tags')
    @else
        <title>{{ $settings->get('branding.site_name') }}</title>
    @endif

    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=5">
    <link rel="icon" type="image/x-icon" href="favicon/icon-144x144.png">
    <link rel="apple-touch-icon" href="favicon/icon-192x192.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color"
          content="rgb({{$bootstrapData->getSelectedTheme()->getHtmlThemeColor()}})">

    @if (file_exists($customCssPath))
        @if ($content = file_get_contents($customCssPath))
            <style>{!! $content !!}</style>
        @endif
    @endif

    @if (file_exists($customHtmlPath))
        @if ($content = file_get_contents($customHtmlPath))
            {!! $content !!}
        @endif
    @endif

    @if ($code = $settings->get('analytics.tracking_code'))
        <!-- Google tag (gtag.js) -->
        <script async
                src="https://www.googletagmanager.com/gtag/js?id={{$settings->get('analytics.tracking_code')}}" />
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());
            gtag('config', '{{$settings->get('analytics.tracking_code')}}');
        </script>
    @endif

    @yield('head-end')
</head>

<body>

<div
    id="root"
    style="{{ $bootstrapData->getSelectedTheme()->getColorsForCss() }}"
    @class(['dark' => $bootstrapData->getSelectedTheme('is_dark')])
>
</div>

<script>
    window.bootstrapData = "{!! $bootstrapData->getEncoded() !!}";
</script>

@viteReactRefresh
@vite('resources/client/main.tsx')

<noscript>You need to have javascript enabled in order to use
    <strong>{{config('app.name')}}</strong>.
</noscript>

@yield('body-end')
</body>
</html>
