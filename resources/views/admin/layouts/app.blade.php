@extends('layouts.base')

@section('body')
@php
    $user = auth()->user();
    $nav = [
        ['label' => 'ড্যাশবোর্ড', 'icon' => '📊', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
        ['label' => 'ইউজার', 'icon' => '👥', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*')],
        ['label' => 'পেমেন্ট রিকোয়েস্ট', 'icon' => '💳', 'route' => 'admin.payments.index', 'active' => request()->routeIs('admin.payments.*')],
        ['label' => 'অ্যাক্টিভিটি লগ', 'icon' => '📊', 'route' => 'admin.activity.index', 'active' => request()->routeIs('admin.activity.*')],
        ['label' => 'ইউজারের মেসেজ', 'icon' => '📨', 'route' => 'admin.feedback.index', 'active' => request()->routeIs('admin.feedback.*')],
        ['label' => 'ওয়ালেট', 'icon' => '👛', 'route' => 'admin.wallets.index', 'active' => request()->routeIs('admin.wallets.*')],
        ['label' => 'সাবস্ক্রিপশন প্ল্যান', 'icon' => '💎', 'route' => 'admin.subscription-plans.index', 'active' => request()->routeIs('admin.subscription-plans.*')],
        ['label' => 'ল্যাব', 'icon' => '⌨️', 'route' => 'admin.labs.index', 'active' => request()->routeIs('admin.labs.*')],
        ['label' => 'প্যাসেজ', 'icon' => '📄', 'route' => 'admin.passages.index', 'active' => request()->routeIs('admin.passages.*')],
        ['label' => 'টাইপিং শুরু কার্ড', 'icon' => '🎯', 'route' => 'admin.practice-cards.index', 'active' => request()->routeIs('admin.practice-cards.*')],
        ['label' => 'টাইপিং অ্যাটেম্পট', 'icon' => '🏆', 'route' => 'admin.attempts.index', 'active' => request()->routeIs('admin.attempts.*')],
        ['label' => 'সেটিংস', 'icon' => '⚙️', 'route' => 'admin.settings.edit', 'active' => request()->routeIs('admin.settings.*')],
    ];
    $pendingCount = \App\Models\PaymentSubmission::where('status', 'pending')->count();
    $feedbackNew = \Illuminate\Support\Facades\Schema::hasTable('feedback_reports') ? \App\Models\FeedbackReport::where('status', 'new')->count() : 0;
@endphp

@include('partials.flash')

<div x-data="{ sidebar: false }" class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="flex items-center gap-3 px-3 sm:px-5 h-16">
            <button type="button" class="lg:hidden p-2 rounded-xl hover:bg-slate-100" @click="sidebar = true" aria-label="মেনু খুলুন">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2 font-bold text-slate-800" style="font-family:'Poppins',sans-serif">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-600 to-blue-500 text-white grid place-items-center text-sm">A</span>
                অ্যাডমিন প্যানেল
            </div>
            <a href="{{ route('dashboard') }}" class="ml-auto text-sm text-slate-500 hover:text-indigo-600 font-medium">← ইউজার সাইটে ফিরুন</a>
        </div>
    </header>

    <div class="flex">
        <aside class="hidden lg:flex flex-col w-60 shrink-0 bg-white border-r border-slate-200 sticky top-16 h-[calc(100vh-4rem)] py-4">
            <nav class="px-3 space-y-1 flex-1 overflow-y-auto">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ $item['active'] ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span class="w-7 text-center">{{ $item['icon'] }}</span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if ($item['route'] === 'admin.feedback.index' && ($feedbackNew ?? 0) > 0)
                            <span class="bg-rose-500 text-white text-[10px] font-bold rounded-full w-5 h-5 grid place-items-center">{{ $feedbackNew }}</span>
                        @endif
                        @if ($item['route'] === 'admin.payments.index' && $pendingCount > 0)
                            <span class="bg-rose-500 text-white text-[10px] font-bold rounded-full w-5 h-5 grid place-items-center">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="px-4 pt-3 border-t border-slate-100 text-xs text-slate-400">লগইন: {{ $user->name }}</div>
        </aside>

        <div x-show="sidebar" x-cloak class="lg:hidden fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/60" x-transition.opacity @click="sidebar = false"></div>
            <aside x-show="sidebar" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                   class="absolute left-0 top-0 h-full w-72 max-w-[85%] bg-white flex flex-col py-4">
                <button type="button" class="absolute right-3 top-3 text-2xl text-slate-400" @click="sidebar = false" aria-label="বন্ধ করুন">&times;</button>
                <nav class="px-3 space-y-1 mt-8">
                    @foreach ($nav as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ $item['active'] ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600' }}">
                            <span class="w-7 text-center">{{ $item['icon'] }}</span>{{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>
        </div>

        <main class="flex-1 min-w-0 p-3 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>
@endsection
