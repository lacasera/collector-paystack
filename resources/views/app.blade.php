<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <title>{{ config('app.name', 'Laravel') }} Billing Portal</title>

    {{-- Read by the pre-built bundle, which cannot know the app name at build time. --}}
    <meta name="collector-app-name" content="{{ config('app.name', 'Laravel') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- Styles -->
    <style>
        {!! file_get_contents($cssPath) !!}
    </style>

    @if (strpos((string) config('collector.brand.color'), '#') === 0)
        <style>
            .bg-custom-hex {
                background-color: {!! config('collector.brand.color') !!};
            }
        </style>
    @endif
    @inertiaHead
</head>

<body class="font-sans antialiased">
@inertia

<!-- Scripts -->
<script>
    {!! file_get_contents($jsPath) !!}
</script>
</body>
</html>
