@php
    // প্রতিটা পেজ চাইলে @section('meta_description', '...'), @section('meta_robots', 'index, follow')
    // দিয়ে এই ডিফল্টগুলো ওভাররাইড করতে পারে। আলাদা কিছু না দিলে নিরাপদ ডিফল্ট ব্যবহার হয়।
    // সতর্কতা: Laravel 11-এ ফ্যাসাড অ্যালিয়াস অটো-লোড হয় না, তাই পুরো namespace দিয়ে কল করা হচ্ছে।
    $seoTitle = trim(\Illuminate\Support\Facades\View::yieldContent('title', config('seo.site_name')));
    $seoFullTitle = $seoTitle.' | '.config('seo.site_name');
    $seoDescription = trim(\Illuminate\Support\Facades\View::yieldContent('meta_description', config('seo.default_description')));
    $seoRobots = trim(\Illuminate\Support\Facades\View::yieldContent('meta_robots', 'noindex, nofollow')); // ডিফল্ট: লগইন-সুরক্ষিত পেজ ইনডেক্স হবে না
    $seoCanonical = url()->current();
    $seoImage = config('seo.og_image') ? url(config('seo.og_image')) : null;
@endphp

<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ config('seo.default_keywords') }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoCanonical }}">

{{-- Open Graph (Facebook/WhatsApp/LinkedIn প্রিভিউ) --}}
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:title" content="{{ $seoFullTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:type" content="website">
<meta property="og:locale" content="bn_BD">
@if ($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoFullTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if ($seoImage)
    <meta name="twitter:image" content="{{ $seoImage }}">
@endif
@if (config('seo.twitter_handle'))
    <meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
@endif

@if (config('seo.google_site_verification'))
    <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
@endif

{{-- Structured Data (JSON-LD) — শুধু ইনডেক্সযোগ্য (পাবলিক) পেজে দেখায়, লগইন-সুরক্ষিত পেজে দরকার নেই --}}
@if (str_contains($seoRobots, 'noindex') === false)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('seo.site_name'),
        'url' => url('/'),
        'description' => config('seo.default_description'),
        'inLanguage' => 'bn',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endif
