{{-- সব পেজের উপরে সরু রিমাইন্ডার স্ট্রিপ (মেয়াদ শেষ / গ্রেস / শীঘ্রই শেষ) — ড্যাশবোর্ড, প্রিমিয়াম ও প্রোফাইল পেজে দেখায় না (সেখানে নিজস্ব কার্ড আছে) --}}
@php
    $stripUser = auth()->user();
    $stripStatus = $stripUser->accountStatus();
    $stripShow = in_array($stripStatus, ['expired', 'grace', 'expiring'], true)
        && ! request()->routeIs('dashboard', 'premium.*', 'profile.*', 'transactions.*');
    $stripStyle = ['expired' => 'from-rose-600 to-orange-500', 'grace' => 'from-orange-500 to-amber-500', 'expiring' => 'from-amber-500 to-yellow-500'][$stripStatus] ?? '';
    $stripText = match ($stripStatus) {
        'expired' => 'মেয়াদ শেষ — আপনার আগের রেজাল্ট দেখতে পারবেন, নতুন টেস্ট দিতে একাউন্ট সক্রিয় করুন।',
        'grace' => 'মেয়াদ শেষ হয়েছে, তবে গ্রেস পিরিয়ডে এখনো টেস্ট দিতে পারবেন — দ্রুত রিনিউ করুন।',
        'expiring' => 'আপনার মেয়াদ আর '.\App\Support\Bn::digits($stripUser->daysLeft()).' দিনের মধ্যে শেষ হবে। এখনই রিনিউ করলে একদিনও নষ্ট হবে না।',
        default => '',
    };
@endphp
@if ($stripShow)
    <a href="{{ route('premium.index') }}" class="block bg-gradient-to-r {{ $stripStyle }} text-white text-center text-xs sm:text-sm font-semibold px-4 py-2 hover:brightness-110 transition anim-fade-in">
        ⏰ {{ $stripText }} <span class="underline underline-offset-2 ml-1 whitespace-nowrap">রিনিউ করুন →</span>
    </a>
@endif
