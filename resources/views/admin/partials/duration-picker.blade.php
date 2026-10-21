{{--
    সময় বাছাইয়ের পিকার (অ্যাডমিন)।
    ব্যবহার:
      @include('admin.partials.duration-picker', ['mode' => 'single', 'name' => 'duration_seconds', 'value' => 180])   // একটি সময় (সেকেন্ডে সেভ হয়)
      @include('admin.partials.duration-picker', ['mode' => 'multi',  'name' => 'durations', 'value' => '1,3,5,10'])  // একাধিক সময় (মিনিট, কমা দিয়ে সেভ হয়)
--}}
@php
    $presets = [1, 2, 3, 5, 10, 15, 20, 30];
    $isMulti = ($mode ?? 'single') === 'multi';
    $initList = $isMulti
        ? collect(explode(',', (string) $value))->map(fn ($v) => (int) trim($v))->filter(fn ($v) => $v >= 1 && $v <= 120)->unique()->sort()->values()->all()
        : [];
    $initSec = $isMulti ? 0 : (int) $value;
@endphp

@if ($isMulti)
    <div x-data='{ list: @json($initList), presets: @json($presets), custom: "",
            toggle(m) { this.list = this.list.includes(m) ? this.list.filter(x => x !== m) : this.list.concat([m]).sort((a, b) => a - b); },
            add() { var v = parseInt(this.custom, 10); if (v >= 1 && v <= 120 && !this.list.includes(v)) { this.list = this.list.concat([v]).sort((a, b) => a - b); } this.custom = ""; } }'>
        <input type="hidden" name="{{ $name }}" :value="list.join(',')">
        <div class="flex flex-wrap gap-2">
            <template x-for="m in presets" :key="m">
                <button type="button" @click="toggle(m)" class="px-3.5 py-1.5 rounded-full text-sm font-semibold border transition"
                        :class="list.includes(m) ? 'bg-indigo-600 border-indigo-600 text-white shadow' : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-300'">
                    <span x-text="m"></span> মিনিট
                </button>
            </template>
            <template x-for="m in list.filter(x => !presets.includes(x))" :key="'c' + m">
                <button type="button" @click="toggle(m)" class="px-3.5 py-1.5 rounded-full text-sm font-semibold border bg-violet-600 border-violet-600 text-white shadow" title="সরাতে ক্লিক করুন">
                    <span x-text="m"></span> মিনিট ✕
                </button>
            </template>
        </div>
        <div class="flex gap-2 mt-2.5">
            <input type="number" min="1" max="120" x-model="custom" @keydown.enter.prevent="add()" placeholder="অন্য সময় (মিনিট)" class="w-44 rounded-xl border-slate-200 text-sm">
            <button type="button" @click="add()" class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold px-4 transition">+ যোগ</button>
        </div>
        <p class="text-[11px] mt-1.5" :class="list.length ? 'text-slate-400' : 'text-amber-600'"
           x-text="list.length ? 'ইউজার এই সময়গুলো থেকে বেছে নিতে পারবে (' + list.join(', ') + ' মিনিট)।' : 'কিছু না বাছলে ডিফল্ট সময় (১, ৩, ৫, ১০ মিনিট) চলবে।'"></p>
    </div>
@else
    <div x-data='{ sec: @json($initSec), presets: @json($presets),
            setMin(v) { v = parseFloat(v); if (v > 0) { this.sec = Math.min(3600, Math.max(30, Math.round(v * 2) * 30)); } } }'>
        <input type="hidden" name="{{ $name }}" :value="sec" id="duration-seconds-input">
        <div class="flex flex-wrap gap-2">
            <template x-for="m in presets" :key="m">
                <button type="button" @click="sec = m * 60" class="px-3.5 py-1.5 rounded-full text-sm font-semibold border transition"
                        :class="sec === m * 60 ? 'bg-indigo-600 border-indigo-600 text-white shadow' : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-300'">
                    <span x-text="m"></span> মিনিট
                </button>
            </template>
        </div>
        <div class="flex items-center gap-2 mt-2.5">
            <label class="text-xs text-slate-500">অথবা নিজে দিন:</label>
            <input type="number" min="0.5" max="60" step="0.5" :value="sec / 60" @change="setMin($event.target.value)" class="w-24 rounded-xl border-slate-200 text-sm">
            <span class="text-xs text-slate-500">মিনিট</span>
            <span class="ml-auto text-xs font-semibold text-indigo-600" x-text="'বর্তমান: ' + (sec / 60) + ' মিনিট'"></span>
        </div>
    </div>
@endif
