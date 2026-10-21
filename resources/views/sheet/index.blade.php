@extends('layouts.app')
@section('title', 'শীট টাইপিং')

@section('content')
@php $canPlay = auth()->user()->canPractice(); @endphp
<div class="max-w-6xl mx-auto">
    <div class="text-center mb-5 anim-fade-up">
        <h1 class="text-2xl sm:text-3xl font-bold">📊 শীট টাইপিং</h1>
        <p class="text-slate-500 text-sm mt-1 max-w-xl mx-auto">স্প্রেডশিটের মতো টেবিল দেখে ঘরে ঘরে টাইপ করুন — অফিস, ব্যাংক ও কম্পিউটার অপারেটরের চাকরির পরীক্ষার জন্য দারুণ অনুশীলন। <b>Tab</b> চাপলে ডানের ঘরে, <b>Enter</b> চাপলে নিচের ঘরে যাবে।</p>
    </div>

    <div class="flex justify-center mb-6 anim-fade-up">
        <div class="inline-flex bg-slate-100 rounded-full p-1 shadow-inner">
            <a href="{{ route('sheet.index', ['lang' => 'bangla']) }}" class="px-5 py-2 rounded-full text-sm font-semibold transition {{ $language === 'bangla' ? 'bg-white shadow text-brand-700' : 'text-slate-500 hover:text-slate-700' }}">🇧🇩 বাংলা</a>
            <a href="{{ route('sheet.index', ['lang' => 'english']) }}" class="px-5 py-2 rounded-full text-sm font-semibold transition {{ $language === 'english' ? 'bg-white shadow text-brand-700' : 'text-slate-500 hover:text-slate-700' }}">🇬🇧 English</a>
        </div>
    </div>

    @if (! $canPlay)
        <div class="mb-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 text-center">
            আপনার মেয়াদ শেষ — শীটগুলো দেখতে পারবেন, কিন্তু টেস্ট দিতে <a href="{{ route('premium.index') }}" class="font-bold underline">একাউন্ট সক্রিয় করুন</a>।
        </div>
    @endif

    @if ($exercises->isEmpty())
        <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center text-slate-500">
            এই ভাষায় এখনো কোনো শীট যোগ করা হয়নি।
            @if (auth()->user()->isAdmin()) <a href="{{ route('admin.sheet-exercises.create') }}" class="text-indigo-600 font-semibold underline">নতুন শীট যোগ করুন</a> @endif
        </div>
    @else
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
            @foreach ($exercises as $ex)
                @php
                    $b = $best[$ex->id] ?? null;
                    $headers = $ex->headers ?: [];
                    $previewCols = min(4, $ex->colCount());
                    $previewRows = array_slice($ex->rows, 0, 3);
                @endphp
                <div class="practice-card rounded-2xl border-2 border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 anim-fade-up flex flex-col" style="animation-delay: {{ $loop->index * 60 }}ms">
                    <div class="flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-xl bg-emerald-100 grid place-items-center text-xl shrink-0">{{ $ex->icon }}</span>
                        <div class="min-w-0">
                            <h3 class="font-bold leading-snug text-slate-800">{{ $ex->title }}</h3>
                            @if ($b && $b['passed']) <span class="text-[11px] font-bold text-emerald-600">✅ পাস করেছেন</span> @endif
                        </div>
                    </div>

                    <p class="text-[13px] leading-relaxed mt-2.5 text-slate-500 min-h-[2.6rem]">{{ $ex->description }}</p>

                    {{-- মিনি প্রিভিউ --}}
                    <div class="mt-3 rounded-xl border border-slate-200 overflow-hidden relative select-none" aria-hidden="true">
                        <table class="w-full text-[11px] text-slate-600">
                            @if ($headers)
                                <thead class="bg-emerald-50 text-emerald-800">
                                    <tr>@foreach (array_slice($headers, 0, $previewCols) as $h)<th class="px-2 py-1 text-left font-semibold truncate max-w-[6rem]">{{ $h }}</th>@endforeach</tr>
                                </thead>
                            @endif
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($previewRows as $row)
                                    <tr>@foreach (array_slice($row, 0, $previewCols) as $cell)<td class="px-2 py-1 truncate max-w-[6rem]">{{ $cell }}</td>@endforeach</tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="absolute inset-x-0 bottom-0 h-5 bg-gradient-to-t from-white to-transparent"></div>
                    </div>

                    <div class="flex flex-wrap gap-1.5 mt-3 text-[11px] font-medium">
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 rounded-full px-2.5 py-1">⏱ {{ \App\Support\Bn::minutes($ex->duration_seconds) }} মিনিট</span>
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 rounded-full px-2.5 py-1">📐 {{ \App\Support\Bn::digits($ex->rowCount()) }}×{{ \App\Support\Bn::digits($ex->colCount()) }} ঘর</span>
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 rounded-full px-2.5 py-1">🚀 {{ \App\Support\Bn::digits($ex->min_wpm) }}+ WPM</span>
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 rounded-full px-2.5 py-1">🎯 {{ \App\Support\Bn::digits($ex->min_accuracy) }}%+</span>
                    </div>

                    <div class="mt-2.5 h-4">
                        @if ($b)
                            <p class="text-[11px] text-slate-400">সেরা: <b class="text-slate-600">{{ \App\Support\Bn::num($b['wpm']) }} WPM</b> · {{ \App\Support\Bn::num($b['accuracy']) }}% · {{ \App\Support\Bn::digits($b['tries']) }}বার চেষ্টা</p>
                        @endif
                    </div>

                    <div class="mt-auto pt-3">
                        @if ($canPlay)
                            <form method="POST" action="{{ route('sheet.start', $ex) }}">
                                @csrf
                                <button class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold py-2.5 shadow-lg shadow-emerald-500/20 transition active:scale-95">শুরু করুন →</button>
                            </form>
                        @else
                            <a href="{{ route('premium.index') }}" class="block text-center w-full rounded-xl bg-slate-200 text-slate-500 font-semibold py-2.5">🔒 মেয়াদ শেষ — রিনিউ করুন</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
