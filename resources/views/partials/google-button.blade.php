<div class="flex items-center gap-3 my-5">
    <div class="h-px flex-1 bg-slate-200"></div>
    <span class="text-xs text-slate-400">অথবা</span>
    <div class="h-px flex-1 bg-slate-200"></div>
</div>
<a href="{{ route('auth.google.redirect') }}"
   class="flex items-center justify-center gap-3 w-full rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 py-2.5 font-semibold text-slate-700">
    <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
        <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.6 5.1 29.6 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.3-.4-3.5z"/>
        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.8 1.1 8 3l6-6C34.6 5.1 29.6 3 24 3 16.3 3 9.6 7.3 6.3 14.7z"/>
        <path fill="#4CAF50" d="M24 45c5.5 0 10.4-1.9 14.2-5.1l-6.6-5.6C29.5 36 26.9 37 24 37c-5.2 0-9.6-3.4-11.2-8l-6.6 5.1C9.5 40.6 16.2 45 24 45z"/>
        <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.6l6.6 5.6C41.9 36.4 45 30.8 45 24c0-1.2-.1-2.3-.4-3.5z"/>
    </svg>
    Google দিয়ে লগইন করুন
</a>
@unless (config('services.google.client_id'))
    <p class="text-[11px] text-slate-400 text-center mt-2">(অ্যাডমিন নোট: Google লগইন সক্রিয় করতে <code class="bg-slate-100 rounded px-1">.env</code> এ <code class="bg-slate-100 rounded px-1">GOOGLE_CLIENT_ID</code>/<code class="bg-slate-100 rounded px-1">SECRET</code> বসান — README দেখুন)</p>
@endunless
