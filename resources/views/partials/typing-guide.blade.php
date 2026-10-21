{{-- হাত বসানোর গাইড: বাটন + পপ-আপ (ছবি ও লেখা)। ভিডিও পপ-আপে নেই — আলাদা লিংক বাটন (অ্যাডমিন সেটিং: guide_video_url)।
     ব্যবহার: @include('partials.typing-guide', ['variant' => 'glass' | 'solid']) --}}
@php
    $variant = $variant ?? 'glass';
    $videoUrl = trim((string) \App\Models\Setting::get('guide_video_url', ''));
    $videoUrl = preg_match('#^https?://#i', $videoUrl) ? $videoUrl : '';
    $isNewUser = auth()->check() && ! \App\Models\TypingAttempt::where('user_id', auth()->id())->exists();

    // কী → [আঙুলের ধরন, আঙুলের নাম]
    $rows = [
        'r1' => [['q','pinky','বাম কনিষ্ঠা'],['w','ring','বাম অনামিকা'],['e','middle','বাম মধ্যমা'],['r','index','বাম তর্জনী'],['t','index','বাম তর্জনী'],['y','index','ডান তর্জনী'],['u','index','ডান তর্জনী'],['i','middle','ডান মধ্যমা'],['o','ring','ডান অনামিকা'],['p','pinky','ডান কনিষ্ঠা']],
        'r2' => [['a','pinky','বাম কনিষ্ঠা'],['s','ring','বাম অনামিকা'],['d','middle','বাম মধ্যমা'],['f','index','বাম তর্জনী'],['g','index','বাম তর্জনী'],['h','index','ডান তর্জনী'],['j','index','ডান তর্জনী'],['k','middle','ডান মধ্যমা'],['l','ring','ডান অনামিকা'],[';','pinky','ডান কনিষ্ঠা']],
        'r3' => [['z','pinky','বাম কনিষ্ঠা'],['x','ring','বাম অনামিকা'],['c','middle','বাম মধ্যমা'],['v','index','বাম তর্জনী'],['b','index','বাম তর্জনী'],['n','index','ডান তর্জনী'],['m','index','ডান তর্জনী'],[',','middle','ডান মধ্যমা'],['.','ring','ডান অনামিকা'],['/','pinky','ডান কনিষ্ঠা']],
    ];
    $home = ['a','s','d','f','j','k','l',';'];
@endphp

<div class="gd-btns" x-data="{ open: false, info: '', fin: '' }" @keydown.escape.window="open = false">
    <button type="button" class="gd-btn {{ $variant }} {{ $isNewUser ? 'is-new' : '' }}" @click="open = true; info = ''; fin = ''">
        ✋ হাত বসানোর গাইড @if ($isNewUser)<span class="gd-badge">নতুনদের জন্য</span>@endif
    </button>
    @if ($videoUrl !== '')
        <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer" class="gd-btn {{ $variant }} video">▶ ভিডিও দেখুন</a>
    @endif

    <template x-teleport="body">
        <div class="gd-overlay" x-show="open" x-cloak x-transition.opacity.duration.200ms @click.self="open = false" role="dialog" aria-modal="true" aria-label="হাত বসানোর গাইড">
            <div class="gd-modal" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-6 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                <div class="gd-head">
                    <span style="font-size:1.7rem">✋</span>
                    <div><b>কীবোর্ডে হাত কীভাবে বসাবেন?</b><small>যেকোনো কী-তে ক্লিক করলে বলে দেবে কোন আঙুল দিয়ে চাপবেন</small></div>
                    <button type="button" class="gd-x" @click="open = false" aria-label="বন্ধ করুন">&times;</button>
                </div>

                <div class="gd-body">
                    <div>
                        <div class="gd-h">⌨️ আঙুল অনুযায়ী কী</div>
                        <div class="gd-kb gd-home-pulse">
                            @foreach ($rows as $rk => $keys)
                                <div class="gd-row {{ $rk }}">
                                    @foreach ($keys as $i => [$k, $type, $name])
                                        <button type="button"
                                                class="gd-key f-{{ $type }} {{ in_array($k, $home, true) ? 'home' : '' }} {{ in_array($k, ['f','j'], true) ? 'bump' : '' }}"
                                                style="--i: {{ $i }}"
                                                :class="info === '{{ strtoupper($k) }} — {{ $name }}' && 'on'"
                                                @click="info = '{{ strtoupper($k) }} — {{ $name }}'; fin = '{{ str_starts_with($name, 'বাম') ? 'L' : 'R' }}-{{ $type }}'">{{ strtoupper($k) }}</button>
                                    @endforeach
                                </div>
                            @endforeach
                            <div class="gd-row"><button type="button" class="gd-key gd-space f-thumb" :class="info === 'স্পেস — দুই বুড়ো আঙুলের যেকোনো একটি' && 'on'" @click="info = 'স্পেস — দুই বুড়ো আঙুলের যেকোনো একটি'; fin = 'thumb'">স্পেস</button></div>
                            @include('partials.typing-hands')
                        </div>
                        <div class="gd-info" style="margin-top:.65rem" x-text="info || '👆 একটা কী-তে ক্লিক করে দেখুন'"></div>
                        <div class="gd-legend" style="margin-top:.6rem">
                            <span><i style="background:#ddd6fe"></i>কনিষ্ঠা</span>
                            <span><i style="background:#bfdbfe"></i>অনামিকা</span>
                            <span><i style="background:#a7f3d0"></i>মধ্যমা</span>
                            <span><i style="background:#fde68a"></i>তর্জনী</span>
                            <span><i style="background:#e2e8f0"></i>বুড়ো আঙুল</span>
                            <span>▭ কালো বর্ডার = হোম রো (শুরুর জায়গা)</span>
                        </div>
                    </div>

                    <div>
                        <div class="gd-h">📌 মনে রাখার ৫টি নিয়ম</div>
                        <ol class="gd-rules">
                            <li><span>সোজা হয়ে বসুন, কবজি হালকা রাখুন এবং কীবোর্ড থেকে হাত ভাসিয়ে রাখুন।</span></li>
                            <li><span>দুই তর্জনী <b>F</b> ও <b>J</b>-এর উঁচু দাগের ওপর রাখুন। বাকি আঙুল পাশের কী-তে (A S D — K L ;) থাকবে।</span></li>
                            <li><span>প্রতিটি কী নির্দিষ্ট আঙুল দিয়ে চাপুন এবং চাপার পর আঙুল হোম রো-তে ফিরিয়ে আনুন।</span></li>
                            <li><span>কীবোর্ডের দিকে না তাকিয়ে পর্দার দিকে তাকান — এতে দ্রুত গতি আসে।</span></li>
                            <li><span>আগে ঠিকভাবে টাইপ করুন, গতি আপনা-আপনি বাড়বে। ভুল হলে ঘাবড়াবেন না!</span></li>
                        </ol>
                    </div>

                    <div class="gd-foot">
                        @if ($videoUrl !== '')
                            <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer" class="gd-btn video">▶ ভিডিও দেখুন</a>
                        @endif
                        <button type="button" class="gd-btn ok" @click="open = false">বুঝেছি, শুরু করি 🚀</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
