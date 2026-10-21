{{-- নতুন ইউজারের জন্য একবারের পপআপ: "টাইপিংয়ে আপনি কেমন?" — শুধু যার কোনো টাইপিং অ্যাটেম্পট নেই, এবং একবার বন্ধ/বাছাই করলে আর আসে না (localStorage: tm_level_seen)। --}}
@if (auth()->check() && ! \App\Models\TypingAttempt::where('user_id', auth()->id())->exists())
<div x-data="levelPicker()" x-init="init()" @keydown.escape.window="close()">
    <template x-teleport="body">
        <div class="lv-overlay" x-show="open" x-cloak x-transition.opacity.duration.250ms @click.self="close()" role="dialog" aria-modal="true" aria-label="আপনার টাইপিং লেভেল">
            <div class="lv-box" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-6 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                <h2>👋 স্বাগতম! টাইপিংয়ে আপনি কেমন?</h2>
                <p>একটা বেছে নিন — আমরা আপনাকে সঠিক জায়গা থেকে শুরু করিয়ে দেব।</p>
                <div class="lv-opts">
                    <a href="{{ route('learn.index') }}" class="lv-opt" @click="pick('new')"><span class="e">🌱</span><span><b>একদম নতুন</b><small>কীবোর্ডে আঙুল কোথায় বসবে সেখান থেকে শুরু। চাপ নেই, টাইমার নেই।</small></span><span class="go">→</span></a>
                    <a href="{{ route('practice.index') }}" class="lv-opt" @click="pick('mid')"><span class="e">🚶</span><span><b>মোটামুটি পারি</b><small>টাইপিং শুরু-র ধাপে ধাপে কার্ড দিয়ে গতি ও নির্ভুলতা বাড়ান।</small></span><span class="go">→</span></a>
                    <a href="{{ route('dashboard') }}" class="lv-opt" @click="pick('pro')"><span class="e">🚀</span><span><b>অভিজ্ঞ / এক্সপার্ট</b><small>সরাসরি পরীক্ষার ল্যাবে গিয়ে নিজের গতি যাচাই করুন।</small></span><span class="go">→</span></a>
                </div>
                <div class="lv-skip">
                    <button type="button" @click="close()">এখন নয়, পরে দেখব</button>
                    <button type="button" @click="close()">আর দেখাব না</button>
                </div>
            </div>
        </div>
    </template>
</div>
<script>
function levelPicker() {
    const KEY = 'tm_level_seen';
    return {
        open: false,
        init() {
            let seen = false; try { seen = !!localStorage.getItem(KEY); } catch (e) {}
            if (!seen) setTimeout(() => { this.open = true; }, 1400);
        },
        mark(v) { try { localStorage.setItem(KEY, v || '1'); } catch (e) {} },
        close() { if (!this.open) return; this.open = false; this.mark('1'); },
        pick(v) { this.mark(v); },
    };
}
</script>
@endif
