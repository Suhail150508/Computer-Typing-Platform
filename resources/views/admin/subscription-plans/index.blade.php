@extends('admin.layouts.app')
@section('title', 'সাবস্ক্রিপশন প্ল্যান')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-bold text-slate-800">সাবস্ক্রিপশন প্ল্যান ({{ $plans->count() }})</h1>
        <a href="{{ route('admin.subscription-plans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2.5 text-sm font-semibold transition">+ নতুন প্ল্যান</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">প্ল্যান</th>
                    <th class="px-4 py-3">মেয়াদ</th>
                    <th class="px-4 py-3">মূল্য</th>
                    <th class="px-4 py-3">ক্রয়</th>
                    <th class="px-4 py-3">স্ট্যাটাস</th>
                    <th class="text-right px-4 py-3">কাজ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($plans as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $p->name }}</td>
                        <td class="px-4 py-3 text-center">{{ $p->duration_days }} দিন</td>
                        <td class="px-4 py-3 text-center">{{ $p->priceLabel() }}</td>
                        <td class="px-4 py-3 text-center">{{ $p->purchases_count }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($p->is_active)
                                <span class="text-xs font-bold text-emerald-600">সক্রিয়</span>
                            @else
                                <span class="text-xs font-bold text-slate-400">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.subscription-plans.edit', $p) }}" class="text-xs font-semibold text-indigo-600 hover:underline">এডিট</a>
                                <form method="POST" action="{{ route('admin.subscription-plans.destroy', $p) }}" onsubmit="return confirm('এই প্ল্যানটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-400 py-8">কোনো প্ল্যান নেই। "নতুন প্ল্যান" চেপে যোগ করুন।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
