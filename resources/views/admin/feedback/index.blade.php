@extends('admin.layouts.app')
@section('title', 'ইউজারের মেসেজ')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ img: null }">
    <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
        <h1 class="text-2xl font-bold text-slate-800">ইউজারের মেসেজ ও সমস্যা</h1>
        @if ($newCount > 0)
            <span class="bg-rose-100 text-rose-700 text-xs font-bold rounded-full px-3 py-1.5">{{ $newCount }}টি নতুন</span>
        @endif
    </div>

    <div class="flex gap-2 mb-5">
        @foreach (['new' => 'নতুন', 'seen' => 'দেখা হয়েছে', 'resolved' => 'সমাধান হয়েছে'] as $key => $label)
            <a href="{{ route('admin.feedback.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($reports as $r)
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5">
                <div class="flex flex-wrap items-center gap-2 text-xs mb-2">
                    <span class="font-bold bg-slate-100 rounded-full px-2.5 py-1">{{ \App\Models\FeedbackReport::TYPES[$r->type] ?? $r->type }}</span>
                    <span class="font-semibold text-slate-700">{{ $r->user?->name ?? 'মুছে ফেলা ইউজার' }}</span>
                    @if ($r->user)<span class="text-slate-400">{{ $r->user->email }}</span>@endif
                    <span class="text-slate-400 ml-auto">{{ $r->created_at->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</span>
                </div>
                <p class="text-sm text-slate-800 whitespace-pre-line break-words">{{ $r->message }}</p>
                @if ($r->screenshot)
                    <button type="button" @click="img = '{{ asset($r->screenshot) }}'" class="mt-3 block">
                        <img src="{{ asset($r->screenshot) }}" alt="স্ক্রিনশট" loading="lazy" class="h-28 rounded-xl border border-slate-200 hover:opacity-90 transition">
                    </button>
                @endif
                @if ($r->page_url)
                    <p class="text-[11px] text-slate-400 mt-3 break-all">📍 {{ $r->page_url }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach (['seen' => 'দেখেছি', 'resolved' => 'সমাধান হয়েছে', 'new' => 'নতুন করুন'] as $s => $l)
                        @if ($r->status !== $s)
                            <form method="POST" action="{{ route('admin.feedback.update', $r) }}">@csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $s }}">
                                <button class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">{{ $l }}</button>
                            </form>
                        @endif
                    @endforeach
                    <form method="POST" action="{{ route('admin.feedback.destroy', $r) }}" onsubmit="return confirm('মেসেজটি মুছে ফেলবেন?')" class="ml-auto">@csrf @method('DELETE')
                        <button class="text-xs font-semibold px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50">মুছুন</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-400">এখানে কোনো মেসেজ নেই 🎉</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $reports->links() }}</div>

    <div x-show="img" x-cloak x-transition.opacity class="fixed inset-0 z-[80] bg-slate-900/80 grid place-items-center p-4" @click="img = null" @keydown.escape.window="img = null">
        <img :src="img" class="max-h-full max-w-full rounded-xl shadow-2xl" alt="">
    </div>
</div>
@endsection
