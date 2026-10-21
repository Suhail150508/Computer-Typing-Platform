@extends('admin.layouts.app')
@section('title', 'শীট টাইপিং')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">📊 শীট টাইপিং ({{ number_format($exercises->total()) }})</h1>
            <p class="text-sm text-slate-500">টেবিল দেখে ঘরে ঘরে টাইপ করার অনুশীলন — Excel থেকে কপি করে সরাসরি পেস্ট করা যায়।</p>
        </div>
        <div class="flex gap-2">
            <form method="GET">
                <select name="language" class="rounded-xl border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">সব ভাষা</option>
                    <option value="bangla" @selected($language === 'bangla')>বাংলা</option>
                    <option value="english" @selected($language === 'english')>English</option>
                </select>
            </form>
            <a href="{{ route('admin.sheet-exercises.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2.5 text-sm font-semibold transition whitespace-nowrap">+ নতুন শীট</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">ক্রম</th>
                    <th class="text-left px-4 py-3">শিরোনাম</th>
                    <th class="px-4 py-3">ভাষা</th>
                    <th class="px-4 py-3">মাপ</th>
                    <th class="px-4 py-3">সময়</th>
                    <th class="px-4 py-3">WPM</th>
                    <th class="px-4 py-3">Accuracy</th>
                    <th class="px-4 py-3">স্ট্যাটাস</th>
                    <th class="text-right px-4 py-3">কাজ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($exercises as $ex)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500">#{{ $ex->order }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-700 max-w-xs truncate">{{ $ex->icon }} {{ $ex->title }}</td>
                        <td class="px-4 py-3 text-center"><span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $ex->language === 'bangla' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">{{ $ex->language === 'bangla' ? 'Bangla' : 'English' }}</span></td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $ex->rowCount() }}×{{ $ex->colCount() }}</td>
                        <td class="px-4 py-3 text-center">{{ \App\Support\Bn::minutes($ex->duration_seconds) }} মিনিট</td>
                        <td class="px-4 py-3 text-center">{{ $ex->min_wpm }}</td>
                        <td class="px-4 py-3 text-center">{{ $ex->min_accuracy }}%</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold {{ $ex->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $ex->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.sheet-exercises.edit', $ex) }}" class="text-xs font-semibold text-indigo-600 hover:underline">এডিট</a>
                                <form method="POST" action="{{ route('admin.sheet-exercises.destroy', $ex) }}" onsubmit="return confirm('এই শীটটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-slate-400 py-8">কোনো শীট নেই। <code>php artisan db:seed</code> চালান বা নতুন শীট যোগ করুন।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $exercises->links() }}</div>
</div>
@endsection
