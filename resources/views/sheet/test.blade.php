@extends('layouts.base')
@section('title', 'শীট টাইপিং')

@push('head')
<style>
    .sheet-wrap { border: 1px solid #cbd5e1; border-radius: 1rem; background: #fff; overflow: auto; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
    table.sheet { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; font-size: var(--sheet-font, 16px); }
    table.sheet th, table.sheet td { border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 0; }
    table.sheet thead th { position: sticky; z-index: 2; background: #f1f5f9; color: #475569; font-weight: 600; padding: .35rem .6rem; text-align: left; white-space: nowrap; }
    table.sheet thead tr:first-child th { top: 0; height: 1.8rem; box-sizing: border-box; font-size: .72em; text-align: center; color: #94a3b8; }
    table.sheet thead tr:nth-child(2) th { top: 1.8rem; background: #e0f2fe; color: #075985; }
    table.sheet .rn { position: sticky; left: 0; z-index: 1; background: #f1f5f9; color: #94a3b8; font-size: .72em; text-align: center; min-width: 2.4rem; padding: 0 .4rem; }
    table.sheet thead .rn { z-index: 3; }
    table.sheet td.ref { padding: .5rem .7rem; white-space: nowrap; user-select: none; -webkit-user-select: none; transition: background .15s; }
    table.sheet td.ref.on { background: #fef08a; box-shadow: inset 0 0 0 2px #f59e0b; }
    table.sheet td.ref.done { background: #ecfdf5; color: #065f46; }
    table.sheet td.cell { min-width: 7rem; }
    table.sheet td.cell input { width: 100%; min-width: 7rem; border: 0; outline: 0; background: transparent; padding: .5rem .7rem; font: inherit; color: #0f172a; }
    table.sheet td.cell:focus-within { box-shadow: inset 0 0 0 2px #2563eb; background: #eff6ff; }
    table.sheet td.cell.filled { background: #f8fafc; }
</style>
@endpush

@section('body')
    @include('partials.flash')

    @php
        $rows = $exercise->rows;
        $headers = $exercise->headers ?: [];
        $cols = $exercise->colCount();
        $letters = fn (int $i) => chr(65 + $i);
    @endphp

    <div id="sheet-root" data-duration="{{ $duration }}" data-rows="{{ count($rows) }}" data-cols="{{ $cols }}" class="min-h-screen flex flex-col gap-3 p-3 sm:p-5">
        {{-- উপরের বার --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 anim-fade-in">
            <div class="text-base sm:text-lg">
                সময়: <b id="timer" class="tabular-nums text-xl" style="font-family:'Poppins',sans-serif">{{ sprintf('%02d:%02d', intdiv($duration, 60), $duration % 60) }}</b> <span class="text-sm">মিনিট</span>
            </div>
            <div class="text-sm text-slate-600">ঘর: <b id="live-cells" class="tabular-nums">0</b>/{{ count($rows) * $cols }}</div>
            <div class="text-sm text-slate-600">লাইভ WPM: <b id="live-wpm" class="tabular-nums">0</b> <span class="text-xs text-slate-400">(আনুমানিক)</span></div>

            <div class="ml-auto flex items-center gap-2">
                <span class="hidden md:inline text-xs text-slate-500">📊 {{ $exercise->title }}</span>
                <button type="button" id="btn-font-down" class="w-9 h-9 rounded-xl bg-white border border-slate-200 font-bold hover:bg-slate-50" title="ফন্ট ছোট">A-</button>
                <button type="button" id="btn-font-up" class="w-9 h-9 rounded-xl bg-white border border-slate-200 font-bold hover:bg-slate-50" title="ফন্ট বড়">A+</button>
                <button type="button" id="btn-content" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold rounded-xl px-5 py-2 shadow transition">Sheet</button>
            </div>
        </div>

        {{-- মূল শীট (দেখে টাইপ করার জন্য) --}}
        <div id="ref-wrap" class="anim-fade-up">
            <p class="text-xs font-semibold text-slate-500 mb-1">📄 মূল শীট — এটা দেখে নিচের খালি শীটে টাইপ করুন (হলুদ ঘর = আপনি এখন যে ঘরে আছেন)</p>
            <div id="ref-scroll" class="sheet-wrap max-h-[30vh] sm:max-h-[34vh] select-none" lang="{{ $exercise->language === 'bangla' ? 'bn' : 'en' }}">
                <table class="sheet" id="ref-table">
                    <thead>
                        <tr>
                            <th class="rn"></th>
                            @for ($c = 0; $c < $cols; $c++)<th>{{ $letters($c) }}</th>@endfor
                        </tr>
                        @if ($headers)
                            <tr>
                                <th class="rn">#</th>
                                @for ($c = 0; $c < $cols; $c++)<th>{{ $headers[$c] ?? '' }}</th>@endfor
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($rows as $r => $row)
                            <tr>
                                <th class="rn">{{ $r + 1 }}</th>
                                @for ($c = 0; $c < $cols; $c++)
                                    <td class="ref" data-r="{{ $r }}" data-c="{{ $c }}">{{ $row[$c] ?? '' }}</td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- টাইপিং শীট --}}
        <div class="flex-1 flex flex-col min-h-[200px]">
            <p class="text-xs font-semibold text-slate-500 mb-1">⌨️ আপনার শীট — <b>Tab</b>: ডানের ঘর · <b>Enter</b>/<b>↓</b>: নিচের ঘর · <b>↑</b>: উপরের ঘর</p>
            <div id="grid-scroll" class="sheet-wrap flex-1 max-h-[44vh]" lang="{{ $exercise->language === 'bangla' ? 'bn' : 'en' }}">
                <table class="sheet" id="grid">
                    <thead>
                        <tr>
                            <th class="rn"></th>
                            @for ($c = 0; $c < $cols; $c++)<th>{{ $letters($c) }}</th>@endfor
                        </tr>
                        @if ($headers)
                            <tr>
                                <th class="rn">#</th>
                                @for ($c = 0; $c < $cols; $c++)<th>{{ $headers[$c] ?? '' }}</th>@endfor
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($rows as $r => $row)
                            <tr>
                                <th class="rn">{{ $r + 1 }}</th>
                                @for ($c = 0; $c < $cols; $c++)
                                    <td class="cell"><input type="text" data-r="{{ $r }}" data-c="{{ $c }}" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" aria-label="সারি {{ $r + 1 }}, কলাম {{ $letters($c) }}"></td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <button type="button" id="btn-finish" class="bg-lime-500 hover:bg-lime-600 active:scale-95 text-white font-bold rounded-xl px-5 py-3 shadow-lg shadow-lime-500/30 transition">Go to Finish</button>
            <p class="text-xs text-slate-400 text-right">কপি-পেস্ট বন্ধ · প্রথম অক্ষর টাইপ করলে টাইমার শুরু · সময় শেষ হলে নিজে থেকেই জমা হবে</p>
        </div>

        <form id="submit-form" method="POST" action="{{ route('sheet.submit') }}" class="hidden">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="cells" value="">
            <input type="hidden" name="elapsed" value="0">
            <input type="hidden" name="speed_samples" value="">
        </form>
    </div>

    <dialog id="confirm-dialog">
        <div class="p-6 text-center">
            <div class="text-4xl mb-2">🏁</div>
            <h2 class="text-lg font-bold">টেস্ট শেষ করবেন?</h2>
            <p class="text-sm text-slate-500 mt-1">জমা দেওয়ার পর আর টাইপ করা যাবে না।</p>
            <div class="grid grid-cols-2 gap-3 mt-5">
                <button type="button" id="btn-cancel" class="rounded-xl border border-slate-200 py-2.5 font-semibold hover:bg-slate-50">না, চালিয়ে যাই</button>
                <button type="button" id="btn-confirm" class="rounded-xl bg-lime-500 hover:bg-lime-600 text-white py-2.5 font-bold">হ্যাঁ, জমা দিন</button>
            </div>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/sheet-typing.js') }}"></script>
@endpush
