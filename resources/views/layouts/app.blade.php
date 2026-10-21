@extends('layouts.base')

@section('body')
    @php $user = auth()->user(); @endphp
    @include('partials.flash')

    <div x-data="{ sidebar: false }" class="min-h-screen">
        {{-- টপবার --}}
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
            <div class="flex items-center gap-3 px-3 sm:px-5 h-16">
                <button type="button" class="lg:hidden p-2 rounded-xl hover:bg-slate-100" @click="sidebar = true" aria-label="মেনু খুলুন">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex items-center gap-2 min-w-0">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 min-w-0 group" title="প্রোফাইল দেখুন">
                        @include('partials.avatar', ['user' => $user, 'size' => 'w-10 h-10'])
                        <span class="hidden md:block text-sm text-slate-700 group-hover:text-brand-600 font-medium truncate max-w-[10rem]">{{ $user->name }}</span>
                    </a>
                </div>

                @php $status = $user->accountStatus(); @endphp
                @if (in_array($status, ['active', 'trial'], true))
                    <span class="bg-emerald-800 text-white text-xs font-bold px-3 py-1.5 rounded-full tracking-wide">ACTIVE</span>
                @elseif ($status === 'expiring')
                    <a href="{{ route('premium.index') }}" class="bg-amber-500 hover:bg-amber-600 transition text-white text-xs font-bold px-3 py-1.5 rounded-full tracking-wide animate-pulse">রিনিউ করুন</a>
                @elseif ($status === 'grace')
                    <a href="{{ route('premium.index') }}" class="bg-orange-500 hover:bg-orange-600 transition text-white text-xs font-bold px-3 py-1.5 rounded-full tracking-wide">GRACE · রিনিউ করুন</a>
                @else
                    <a href="{{ route('premium.index') }}" class="bg-rose-600 hover:bg-rose-700 transition text-white text-xs font-bold px-3 py-1.5 rounded-full tracking-wide">EXPIRED · রিনিউ করুন</a>
                @endif
                @if ($user->hasActiveSubscription())
                    <div class="hidden sm:flex items-center gap-2 bg-slate-100 rounded-full px-3 py-1.5 text-xs">
                        <span class="text-slate-500">মেয়াদ:</span>
                        <b class="text-brand-700">{{ $user->subscription_expires_at->format('d M Y') }}</b>
                        <span class="bg-white rounded-full px-2 py-0.5 text-slate-600">{{ \App\Support\Bn::digits($user->daysLeft()) }} দিন বাকি</span>
                    </div>
                @endif

                <div class="ml-auto flex items-center gap-2">
                    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-1.5 text-sm font-semibold text-emerald-800" title="ওয়ালেট ব্যালেন্স">
                        <span>👛</span> {{ \App\Support\Bn::num($user->wallet_balance) }} ৳
                    </div>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 rounded-2xl px-3 py-2 text-sm font-semibold transition">
                            <span class="hidden sm:inline">Profile</span>
                            <svg class="w-4 h-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-1.5rem)] bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 z-50">
                            <div class="rounded-xl bg-gradient-to-r from-indigo-50 to-sky-50 px-3 py-3 mb-2 flex items-center gap-3">
                                @include('partials.avatar', ['user' => $user, 'size' => 'w-11 h-11'])
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-sm truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-dashed border-brand-500/40 bg-brand-50 px-3 py-2 mb-2" x-data="{ copied: false }">
                                <span class="text-[11px] font-bold tracking-widest text-slate-500">TYPE CODE</span>
                                <button type="button" class="bg-brand-600 text-white text-sm font-bold rounded-full px-3 py-0.5 hover:bg-brand-700 transition"
                                        @click="navigator.clipboard && navigator.clipboard.writeText('{{ $user->type_code }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                        title="কপি করতে ক্লিক করুন"><span x-text="copied ? 'কপি হয়েছে ✓' : '{{ $user->type_code }}'">{{ $user->type_code }}</span></button>
                            </div>
                            @foreach ([
                                ['profile.edit', '👤', 'প্রোফাইল সেটিংস', null, 'bg-indigo-100'],
                                ['premium.index', '💎', $user->hasActiveSubscription() ? 'প্যাকেজ / রিনিউ' : 'একাউন্ট অ্যাক্টিভ করুন', null, 'bg-fuchsia-100'],
                                ['transactions.index', '🧾', 'ট্রানজেকশন হিস্ট্রি', null, 'bg-emerald-100'],
                                ['wpm.index', '📈', 'WPM Viewer', null, 'bg-sky-100'],
                                ['profile.edit', '🔒', 'পাসওয়ার্ড পরিবর্তন', '#password', 'bg-amber-100'],
                                ['tips.index', '📘', 'নির্দেশিকা (স্পিড টিপস)', null, 'bg-rose-100'],
                            ] as [$r, $ico, $lbl, $hash, $bg])
                                <a href="{{ route($r).($hash ?? '') }}" class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-700">
                                    <span class="w-8 h-8 rounded-lg {{ $bg }} grid place-items-center text-base">{{ $ico }}</span>{{ $lbl }}
                                </a>
                            @endforeach
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 pt-1 border-t border-slate-100">
                                @csrf
                                <button class="w-full flex items-center gap-3 px-2 py-2 rounded-xl text-sm text-rose-600 hover:bg-rose-50 font-semibold">
                                    <span class="w-8 h-8 rounded-lg bg-rose-100 grid place-items-center text-base">🚪</span>লগআউট
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @include('partials.marquee')
        @include('partials.subscription-strip')

        <div class="flex">
            {{-- ডেস্কটপ সাইডবার --}}
            <aside class="hidden lg:flex flex-col w-64 shrink-0 bg-nav text-white sticky top-[6.25rem] h-[calc(100vh-6.25rem)]">
                @include('partials.sidebar')
            </aside>

            {{-- মোবাইল সাইডবার --}}
            <div x-show="sidebar" x-cloak class="lg:hidden fixed inset-0 z-50">
                <div class="absolute inset-0 bg-slate-900/60" x-transition.opacity @click="sidebar = false"></div>
                <aside x-show="sidebar" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                       x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                       class="absolute left-0 top-0 h-full w-72 max-w-[85%] bg-nav text-white flex flex-col">
                    <button type="button" class="absolute right-3 top-3 text-2xl text-white/80" @click="sidebar = false" aria-label="বন্ধ করুন">&times;</button>
                    @include('partials.sidebar')
                </aside>
            </div>

            <main class="flex-1 min-w-0 p-3 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @include('partials.feedback-widget')
@endsection
