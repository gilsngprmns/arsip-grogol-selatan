<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Sistem internal arsip dokumen Kelurahan Grogol Selatan untuk petugas yang berwenang.">
        <meta name="theme-color" content="#0B1F3A">

        <title inertia>{{ config('app.name', 'Arsip Dokumen Grogol Selatan') }}</title>

        <link rel="icon" href="/images/logo-kelurahan-icon-512.png" type="image/png">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/logo-kelurahan-icon-180.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|source-serif-4:600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased text-slate-900">
        @inertia
    </body>
</html>
