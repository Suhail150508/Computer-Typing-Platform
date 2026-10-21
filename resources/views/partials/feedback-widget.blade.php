{{-- ইউজারের সমস্যা/মেসেজ পাঠানোর ফ্লোটিং বক্স (ডান নিচে) — সব ক্লাস fb- প্রিফিক্সের, অন্য ডিজাইনে প্রভাব ফেলে না --}}
<style>
.fb-root{position:fixed;right:1rem;bottom:1rem;z-index:60;font-family:inherit}
.fb-btn{position:relative;display:flex;align-items:center;gap:.5rem;border:0;cursor:pointer;color:#fff;font-weight:700;font-size:.85rem;padding:.8rem 1.1rem;border-radius:999px;background:linear-gradient(135deg,#6366f1,#2563eb);box-shadow:0 10px 25px -8px rgba(79,70,229,.6);transition:transform .2s,box-shadow .2s}
.fb-btn:hover{transform:translateY(-2px) scale(1.03);box-shadow:0 14px 30px -8px rgba(79,70,229,.7)}
.fb-btn::before{content:"";position:absolute;inset:0;border-radius:inherit;background:inherit;z-index:-1;animation:fbPulse 2.6s ease-out infinite}
.fb-ico{font-size:1.15rem;display:inline-block;animation:fbWiggle 4s ease-in-out infinite}
.fb-panel{position:absolute;right:0;bottom:calc(100% + .75rem);width:min(22rem,calc(100vw - 2rem));max-height:calc(100vh - 6rem);overflow:auto;background:#fff;border:1px solid #dbeafe;border-radius:1.25rem;box-shadow:0 25px 60px -15px rgba(30,41,59,.35);transform-origin:bottom right}
.fb-head{padding:1rem 1.1rem;color:#fff;background:linear-gradient(135deg,#6366f1,#2563eb);border-radius:1.25rem 1.25rem 0 0;display:flex;align-items:flex-start;gap:.6rem}
.fb-head b{display:block;font-size:.95rem}.fb-head small{opacity:.85;font-size:.72rem}
.fb-x{margin-left:auto;background:rgba(255,255,255,.18);border:0;color:#fff;width:1.7rem;height:1.7rem;border-radius:50%;cursor:pointer;font-size:1.1rem;line-height:1}
.fb-body{padding:1rem 1.1rem 1.1rem;display:grid;gap:.75rem}
.fb-types{display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem}
.fb-type{border:1.5px solid #e2e8f0;background:#fff;border-radius:.8rem;padding:.5rem .2rem;font-size:.72rem;font-weight:700;color:#475569;cursor:pointer;transition:all .2s}
.fb-type:hover{border-color:#a5b4fc}.fb-type.on{border-color:#6366f1;background:#eef2ff;color:#4338ca;transform:translateY(-1px)}
.fb-ta{width:100%;min-height:6rem;resize:vertical;border:1.5px solid #e2e8f0;border-radius:.8rem;padding:.65rem .75rem;font-size:.85rem;outline:none;transition:border-color .2s,box-shadow .2s}
.fb-ta:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.fb-drop{border:1.5px dashed #c7d2fe;background:#f8faff;border-radius:.8rem;padding:.7rem;text-align:center;font-size:.75rem;color:#64748b;cursor:pointer;transition:all .2s}
.fb-drop:hover{background:#eef2ff;border-color:#818cf8}
.fb-prev{position:relative}.fb-prev img{width:100%;max-height:9rem;object-fit:contain;border-radius:.8rem;border:1px solid #e2e8f0;background:#f8fafc}
.fb-prev button{position:absolute;top:.35rem;right:.35rem;width:1.5rem;height:1.5rem;border-radius:50%;border:0;background:rgba(15,23,42,.7);color:#fff;cursor:pointer}
.fb-send{border:0;cursor:pointer;color:#fff;font-weight:700;font-size:.88rem;padding:.75rem;border-radius:.9rem;background:linear-gradient(135deg,#6366f1,#2563eb);transition:transform .15s,opacity .2s}
.fb-send:hover{transform:translateY(-1px)}.fb-send[disabled]{opacity:.6;cursor:wait}
.fb-err{color:#e11d48;font-size:.75rem}
.fb-ok{padding:2rem 1.2rem;text-align:center}
.fb-ok .t{width:4rem;height:4rem;margin:0 auto .8rem;border-radius:50%;background:#10b981;display:grid;place-items:center;color:#fff;font-size:2rem;animation:fbPop .5s cubic-bezier(.2,1.6,.4,1)}
@keyframes fbPulse{0%{transform:scale(1);opacity:.5}100%{transform:scale(1.5);opacity:0}}
@keyframes fbWiggle{0%,86%,100%{transform:rotate(0)}90%{transform:rotate(-14deg)}94%{transform:rotate(12deg)}97%{transform:rotate(-6deg)}}
@keyframes fbPop{0%{transform:scale(0)}100%{transform:scale(1)}}
@media (prefers-reduced-motion:reduce){.fb-btn::before,.fb-ico{animation:none}.fb-btn,.fb-type,.fb-send{transition:none}}
@media (max-width:480px){.fb-root{right:.75rem;bottom:.75rem}.fb-lbl{display:none}.fb-btn{padding:.85rem}}
</style>

<div class="fb-root" x-data="feedbackBox()" @keydown.escape.window="open = false" @paste.window="onPaste($event)">
    <div class="fb-panel" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-90 translate-y-3" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        <div class="fb-head">
            <span style="font-size:1.5rem">🛠️</span>
            <div><b>সমস্যা বা মতামত জানান</b><small>স্ক্রিনশটসহ পাঠালে দ্রুত সমাধান করা যায়</small></div>
            <button type="button" class="fb-x" @click="open = false" aria-label="বন্ধ করুন">&times;</button>
        </div>

        <div class="fb-ok" x-show="done" x-cloak>
            <div class="t">✓</div>
            <b style="color:#0f172a">ধন্যবাদ!</b>
            <p style="font-size:.8rem;color:#64748b;margin-top:.25rem">আপনার মেসেজ অ্যাডমিনের কাছে পৌঁছে গেছে।</p>
        </div>

        <form class="fb-body" x-show="!done" @submit.prevent="send()">
            <div class="fb-types">
                <template x-for="t in types" :key="t.k">
                    <button type="button" class="fb-type" :class="type === t.k && 'on'" @click="type = t.k" x-text="t.l"></button>
                </template>
            </div>
            <textarea class="fb-ta" x-model="message" maxlength="2000" placeholder="কী সমস্যা হচ্ছে বা কী পরামর্শ আছে, এখানে লিখুন..." required></textarea>

            <template x-if="!preview">
                <label class="fb-drop">
                    📎 স্ক্রিনশট যোগ করুন <span style="display:block;font-size:.68rem;opacity:.8">ক্লিক করুন বা Ctrl+V দিয়ে পেস্ট করুন</span>
                    <input type="file" accept="image/png,image/jpeg,image/webp" class="hidden" style="display:none" @change="pick($event.target.files[0])">
                </label>
            </template>
            <div class="fb-prev" x-show="preview" x-cloak>
                <img :src="preview" alt="স্ক্রিনশট প্রিভিউ">
                <button type="button" @click="clearFile()" aria-label="সরান">&times;</button>
            </div>

            <p class="fb-err" x-show="error" x-text="error" x-cloak></p>
            <button class="fb-send" :disabled="busy"><span x-show="!busy">📨 পাঠিয়ে দিন</span><span x-show="busy" x-cloak>পাঠানো হচ্ছে...</span></button>
        </form>
    </div>

    <button type="button" class="fb-btn" @click="toggle()" :aria-expanded="open" aria-label="সমস্যা বা মতামত জানান">
        <span class="fb-ico" x-text="open ? '✕' : '💬'"></span><span class="fb-lbl">সমস্যা জানান</span>
    </button>
</div>

<script>
function feedbackBox() {
    return {
        open: false, done: false, busy: false, error: '',
        type: 'problem', message: '', file: null, preview: null,
        types: [{k: 'problem', l: '⚠️ সমস্যা'}, {k: 'idea', l: '💡 পরামর্শ'}, {k: 'other', l: '💬 অন্যান্য'}],
        toggle() { this.open = !this.open; if (this.open && this.done) this.reset(); },
        reset() { this.done = false; this.message = ''; this.error = ''; this.clearFile(); },
        async pick(f) {
            this.error = '';
            if (!f) return;
            if (!/^image\/(png|jpe?g|webp)$/.test(f.type)) { this.error = 'শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।'; return; }
            if (f.size > 20 * 1024 * 1024) { this.error = 'ছবির সাইজ ২০ MB-এর বেশি হতে পারবে না।'; return; }
            this.clearFile();
            // হোস্টিংয়ে আপলোড সীমা ছোট থাকে, তাই ছবি ব্রাউজারেই ছোট (সর্বোচ্চ ১৬০০px, JPEG) করে নেওয়া হয়
            const small = await this.shrink(f);
            if (small.size > 1.8 * 1024 * 1024) { this.error = 'ছবিটি এখনো বড়। ছোট একটি অংশের স্ক্রিনশট নিন।'; return; }
            this.file = small;
            this.preview = URL.createObjectURL(small);
        },
        shrink(f) {
            return new Promise((resolve) => {
                const url = URL.createObjectURL(f);
                const img = new Image();
                img.onload = () => {
                    try {
                        const max = 1600, r = Math.min(1, max / Math.max(img.width, img.height));
                        if (r === 1 && f.size < 700 * 1024) { URL.revokeObjectURL(url); return resolve(f); }
                        const c = document.createElement('canvas');
                        c.width = Math.round(img.width * r); c.height = Math.round(img.height * r);
                        const ctx = c.getContext('2d');
                        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
                        ctx.drawImage(img, 0, 0, c.width, c.height);
                        URL.revokeObjectURL(url);
                        c.toBlob((b) => {
                            if (!b || b.size >= f.size) return resolve(f);
                            resolve(new File([b], 'screenshot.jpg', {type: 'image/jpeg'}));
                        }, 'image/jpeg', 0.82);
                    } catch (e) { URL.revokeObjectURL(url); resolve(f); }
                };
                img.onerror = () => { URL.revokeObjectURL(url); resolve(f); };
                img.src = url;
            });
        },
        clearFile() { if (this.preview) URL.revokeObjectURL(this.preview); this.file = null; this.preview = null; },
        onPaste(e) {
            if (!this.open) return;
            const item = Array.from((e.clipboardData || {}).items || []).find(i => i.type.startsWith('image/'));
            if (item) { this.pick(item.getAsFile()); }
        },
        async send() {
            if (this.busy) return;
            this.error = '';
            if (this.message.trim().length < 5) { this.error = 'আরেকটু বিস্তারিত লিখুন (কমপক্ষে ৫ অক্ষর)।'; return; }
            this.busy = true;
            const post = async (token) => {
                const fd = new FormData();
                fd.append('_token', token);
                fd.append('type', this.type);
                fd.append('message', this.message.trim());
                fd.append('page_url', location.href);
                if (this.file) fd.append('screenshot', this.file, this.file.name || 'screenshot.png');
                return fetch(@json(route('feedback.store', [], false)), {
                    method: 'POST', body: fd, credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token},
                });
            };
            try {
                let res = await post(document.querySelector('meta[name="csrf-token"]')?.content || '');
                if (res.status === 419) {
                    // পেজ অনেকক্ষণ খোলা থাকলে টোকেন পুরনো হয়ে যায় — নতুন টোকেন এনে একবার আবার চেষ্টা
                    const t = await fetch(@json(route('feedback.token', [], false)) + '?t=' + Date.now(), {credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json'}});
                    if (t.ok) {
                        const j = await t.json();
                        const m = document.querySelector('meta[name="csrf-token"]'); if (m) m.content = j.token;
                        res = await post(j.token);
                    }
                }
                const data = await res.json().catch(() => ({}));
                if (res.ok) {
                    this.done = true; this.message = ''; this.clearFile();
                    setTimeout(() => { this.open = false; }, 2200);
                } else if (res.status === 419 || res.status === 401) {
                    this.error = 'সেশনের মেয়াদ শেষ হয়েছে। পেজটি রিফ্রেশ (F5) করে আবার পাঠান।';
                } else {
                    const first = data.errors ? Object.values(data.errors)[0][0] : null;
                    this.error = first || data.message || 'পাঠানো যায়নি, আবার চেষ্টা করুন।';
                }
            } catch (e) {
                this.error = 'ইন্টারনেট সংযোগ পরীক্ষা করে আবার চেষ্টা করুন।';
            } finally { this.busy = false; }
        },
    };
}
</script>
