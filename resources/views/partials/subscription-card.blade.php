{{-- ড্যাশবোর্ডের বড় সাবস্ক্রিপশন কার্ড: মেয়াদ শেষ / গ্রেস / শীঘ্রই শেষ — সবসময় বন্ধুত্বপূর্ণ সুর, কখনো "আটকানো" নয় --}}
@php
    $cardUser = auth()->user();
    $cardStatus = $cardUser->accountStatus();
@endphp
@if (in_array($cardStatus, ['expired', 'grace', 'expiring'], true))
    @php
        $cfg = [
            'expired' => ['from-rose-600 to-orange-500', '⏰', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে', 'এখনো আপনি আপনার সব রেজাল্ট, WPM গ্রাফ ও স্পিড টিপস দেখতে পারবেন। নতুন টেস্ট দিতে একাউন্ট সক্রিয় করুন — মাত্র কয়েক ধাপে।', 'shadow-rose-500/30'],
            'grace' => ['from-orange-500 to-amber-500', '🕒', 'গ্রেস পিরিয়ড চলছে', 'মেয়াদ শেষ হয়েছে, তবে এখনো টেস্ট দিতে পারছেন। গ্রেস পিরিয়ড শেষ হওয়ার আগেই রিনিউ করে নিন।', 'shadow-orange-500/30'],
            'expiring' => ['from-amber-500 to-yellow-500', '🔔', 'মেয়াদ শীঘ্রই শেষ হবে — আর '.\App\Support\Bn::digits($cardUser->daysLeft()).' দিন', 'এখন রিনিউ করলে নতুন মেয়াদ বর্তমান মেয়াদের সাথে যোগ হবে, একদিনও নষ্ট হবে না।', 'shadow-amber-500/30'],
        ][$cardStatus];
    @endphp
    <a href="{{ route('premium.index') }}" class="mb-5 flex flex-wrap items-center gap-3 rounded-2xl bg-gradient-to-r {{ $cfg[0] }} text-white px-5 py-4 shadow-lg {{ $cfg[4] }} hover:shadow-xl transition anim-fade-in">
        <span class="text-3xl">{{ $cfg[1] }}</span>
        <div class="flex-1 min-w-[200px]">
            <p class="font-bold">{{ $cfg[2] }}</p>
            <p class="text-sm text-white/90">{{ $cfg[3] }}</p>
            @if ($cardStatus !== 'expiring' && ($stats['attempts'] ?? 0) > 0)
                <p class="text-xs text-white/80 mt-1">আপনার এ পর্যন্ত অর্জন: {{ \App\Support\Bn::digits($stats['attempts']) }}টি টেস্ট · সেরা {{ \App\Support\Bn::num($stats['best_wpm']) }} WPM — থেমে যাবেন না! 💪</p>
            @endif
        </div>
        <span class="bg-white text-slate-800 font-bold rounded-full px-5 py-2 text-sm shrink-0">{{ $cardStatus === 'expiring' ? 'রিনিউ করুন →' : 'এখনই সক্রিয় করুন →' }}</span>
    </a>
@endif
