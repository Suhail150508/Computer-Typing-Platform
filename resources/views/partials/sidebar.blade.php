@php
    $items = [
        ['label' => 'টাইপিং ল্যাব', 'icon' => '⌨️', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard', 'typing.*')],
        ['label' => 'অনুশীলন (নতুনদের জন্য)', 'icon' => '✨', 'route' => 'learn.index', 'active' => request()->routeIs('learn.*')],
        ['label' => 'টাইপিং শুরু', 'icon' => '🎯', 'route' => 'practice.index', 'active' => request()->routeIs('practice.*')],
        ['label' => 'শীট দেখে টাইপ', 'icon' => '📄', 'route' => 'free.index', 'active' => request()->routeIs('free.*')],
        ['label' => 'টাইপিং রেজাল্ট', 'icon' => '🏆', 'route' => 'results.index', 'active' => request()->routeIs('results.*')],
        ['label' => 'WPM Viewer', 'icon' => '📈', 'route' => 'wpm.index', 'active' => request()->routeIs('wpm.*')],
        ['label' => 'স্পিড বাড়ানোর উপায়', 'icon' => '💡', 'route' => 'tips.index', 'active' => request()->routeIs('tips.*')],
        ['label' => 'আমার প্রোফাইল', 'icon' => '👤', 'route' => 'profile.edit', 'active' => request()->routeIs('profile.*', 'transactions.*')],
        ['label' => 'প্রিমিয়াম / প্যাকেজ', 'icon' => '💎', 'route' => 'premium.index', 'active' => request()->routeIs('premium.*')],
    ];
@endphp

<div class="px-3 pt-4">
    <div class="rounded-2xl bg-white/15 p-3 text-center shadow-inner">
        <span class="inline-block bg-white text-brand-700 font-bold rounded-full px-4 py-1 text-sm" style="font-family:'Poppins',sans-serif">TYPING MOJA</span>
    </div>
</div>

<nav class="px-3 py-4 space-y-2 overflow-y-auto flex-1">
    @foreach ($items as $it)
        <a href="{{ route($it['route']) }}" class="side-link {{ $it['active'] ? 'active' : '' }}">
            <span class="side-ico">{{ $it['icon'] }}</span>
            <span class="flex-1">{{ $it['label'] }}</span>
        </a>
    @endforeach

    @if (auth()->user()?->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="side-link" style="background:rgba(255,255,255,.1)">
            <span class="side-ico">⚙️</span>
            <span class="flex-1">অ্যাডমিন প্যানেল</span>
        </a>
    @endif
</nav>
