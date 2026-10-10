<!doctype html>
<html lang="en-AU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->seo_title ?: $page->title }}</title>
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    <meta name="robots" content="{{ $page->robots ?: 'index, follow' }}">
    <link rel="canonical" href="{{ $page->canonicalUrl() }}">
    @yield('head')
</head>
<body>
    @yield('content')
</body>
</html>
