<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TypingMoja') | {{ config('app.name') }}</title>
    @include('partials.seo-meta')

    {{-- ফেভিকন: আলাদা ইমেজ ফাইল ছাড়াই ইমোজি-ভিত্তিক SVG ফেভিকন --}}
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%E2%8C%A8%EF%B8%8F%3C/text%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    {{-- ডেভেলপমেন্টের জন্য Tailwind CDN। প্রোডাকশনে Vite/Tailwind বিল্ডে নিয়ে যাওয়া ভালো (README দেখুন) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { nav: '#1f3f7f', brand: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' } },
                fontFamily: { sans: ['Hind Siliguri', 'Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
            } }
        };
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script defer src="{{ asset('js/count-up.js') }}?v={{ @filemtime(public_path('js/count-up.js')) }}"></script>
    <script defer src="{{ asset('js/confetti.js') }}?v={{ @filemtime(public_path('js/confetti.js')) }}"></script>
    @stack('head')
</head>
<body class="bg-app min-h-screen antialiased">
    @yield('body')
    @stack('scripts')
</body>
</html>
