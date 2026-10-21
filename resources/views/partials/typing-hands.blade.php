{{-- দুই হাতের অ্যানিমেটেড ছবি (SVG)। বাবা partial-এর Alpine state `fin` ('L-index', 'R-pinky', 'thumb'...) দিয়ে আঙুল হাইলাইট হয়। --}}
@php
    // [type, বামহাতের লেখা, cx, tipY, দ্বিতীয় লেখা]
    $fingers = [
        ['pinky', 'A', 62, 112, ''],
        ['ring', 'S', 108, 90, ''],
        ['middle', 'D', 154, 80, ''],
        ['index', 'F', 200, 90, 'G'],
    ];
    $right = ['pinky' => [';', ''], 'ring' => ['L', ''], 'middle' => ['K', ''], 'index' => ['J', 'H']];
    $col = ['pinky' => '#c4b5fd', 'ring' => '#93c5fd', 'middle' => '#6ee7b7', 'index' => '#fcd34d'];
@endphp
<svg class="gd-hands" viewBox="0 62 600 212" role="img" aria-label="হোম রো-তে দুই হাত বসানোর ছবি">
    <text class="cap" x="140" y="262">বাম হাত</text>
    <text class="cap" x="460" y="262">ডান হাত</text>

    @foreach (['L', 'R'] as $side)
        @php $m = $side === 'R'; @endphp
        <path class="palm" d="M {{ $m ? 378 : 40 }} 178 h 182 v 40 a 28 28 0 0 1 -28 28 h -126 a 28 28 0 0 1 -28 -28 z" />
        {{-- বুড়ো আঙুল --}}
        <g class="fg" :class="fin === 'thumb' && 'act'" style="--c:#e2e8f0; --d: {{ $m ? '1.9' : '1.6' }}s">
            <rect x="{{ $m ? 351 : 215 }}" y="170" width="34" height="82" rx="17" transform="rotate({{ $m ? -38 : 38 }} {{ $m ? 368 : 232 }} 211)" />
        </g>
        @foreach ($fingers as $n => [$type, $lab, $cx, $tip, $sec])
            @php
                $x = $m ? 600 - $cx : $cx;
                $t = $m ? $right[$type][0] : $lab;
                $s2 = $m ? $right[$type][1] : $sec;
                $order = $m ? 4 + (3 - $n) : $n;
            @endphp
            <g class="fg" :class="fin === '{{ $side }}-{{ $type }}' && 'act'" style="--c: {{ $col[$type] }}; --d: {{ number_format($order * .18, 2) }}s">
                <rect x="{{ $x - 18 }}" y="{{ $tip }}" width="36" height="{{ 196 - $tip }}" rx="18" />
                <text x="{{ $x }}" y="{{ $tip + 28 }}" font-size="17">{{ $t }}</text>
                @if ($s2 !== '')<text x="{{ $x }}" y="{{ $tip + 52 }}" font-size="12" style="opacity:.55">{{ $s2 }}</text>@endif
                @if (in_array($t, ['F', 'J'], true))<rect x="{{ $x - 9 }}" y="{{ $tip + 34 }}" width="18" height="3" rx="2" style="fill:#1e293b;stroke:none" />@endif
            </g>
        @endforeach
    @endforeach
</svg>
