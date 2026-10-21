@extends('admin.layouts.app')
@section('title', 'পেমেন্ট রিকোয়েস্ট')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
        <h1 class="text-2xl font-bold text-slate-800">পেমেন্ট রিকোয়েস্ট</h1>
        @if ($pendingCount > 0)
            <span class="bg-rose-100 text-rose-700 text-xs font-bold rounded-full px-3 py-1.5">{{ $pendingCount }}টি অপেক্ষমাণ</span>
        @endif
    </div>

    {{-- ফিল্টার ট্যাব --}}
    <div class="flex gap-2 mb-5">
        @foreach (['pending' => 'অপেক্ষমাণ', 'approved' => 'অনুমোদিত', 'rejected' => 'বাতিল'] as $key => $label)
            <a href="{{ route('admin.payments.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">ইউজার</th>
                    <th class="px-4 py-3">প্যাকেজ</th>
                    <th class="px-4 py-3">প্ল্যাটফর্ম</th>
                    <th class="px-4 py-3">প্রেরকের নম্বর</th>
                    <th class="px-4 py-3">Transaction ID</th>
                    <th class="px-4 py-3">পরিমাণ</th>
                    <th class="px-4 py-3">সময়</th>
                    @if ($status === 'pending')
                        <th class="text-right px-4 py-3">কাজ</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($submissions as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-700">{{ $s->user->name }}</div>
                            <div class="text-xs text-slate-400">{{ $s->user->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $s->plan->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">{{ $s->platformLabel() }}</td>
                        <td class="px-4 py-3 text-center font-mono">{{ $s->sender_number }}</td>
                        <td class="px-4 py-3 text-center font-mono">{{ $s->transaction_id }}</td>
                        <td class="px-4 py-3 text-center font-semibold">{{ \App\Support\Bn::num($s->amount) }} ৳</td>
                        <td class="px-4 py-3 text-center text-xs text-slate-400">{{ $s->created_at->diffForHumans() }}</td>
                        @if ($status === 'pending')
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.payments.approve', $s) }}" onsubmit="return confirm('এই পেমেন্ট অনুমোদন করলে ইউজারের একাউন্ট সাথে সাথে সক্রিয় হয়ে যাবে। নিশ্চিত?')">
                                        @csrf
                                        <button class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-3 py-1.5 transition">✓ অনুমোদন</button>
                                    </form>
                                    <button type="button" class="text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg px-3 py-1.5 transition" onclick="document.getElementById('reject-{{ $s->id }}').showModal()">✕ বাতিল</button>
                                </div>

                                <dialog id="reject-{{ $s->id }}" class="rounded-2xl p-0 backdrop:bg-slate-900/50">
                                    <form method="POST" action="{{ route('admin.payments.reject', $s) }}" class="p-5 w-80">
                                        @csrf
                                        <h3 class="font-bold mb-2">আবেদন বাতিল করবেন?</h3>
                                        <textarea name="admin_note" class="field text-sm" rows="3" placeholder="কারণ লিখুন (ঐচ্ছিক)"></textarea>
                                        <div class="flex gap-2 mt-3">
                                            <button type="button" class="flex-1 rounded-xl border border-slate-200 py-2 text-sm font-semibold" onclick="this.closest('dialog').close()">বাতিল</button>
                                            <button class="flex-1 rounded-xl bg-rose-600 text-white py-2 text-sm font-semibold">নিশ্চিত করুন</button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">এই অবস্থায় কোনো রিকোয়েস্ট নেই।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $submissions->links() }}</div>
</div>
@endsection
