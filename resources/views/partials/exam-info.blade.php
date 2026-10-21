{{-- "সরকারি টাইপিং পরীক্ষা: যা জানা জরুরি" — অ্যানিমেটেড বাটন + পপআপ। নতুন মেনু নেই।
     ব্যবহার: @include('partials.exam-info') --}}
<div class="ex-wrap" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" class="ex-btn" @click="open = true">
        <span class="ex-ic">📢</span>
        <span class="ex-tx"><b>সরকারি পরীক্ষায় কী জানা জরুরি?</b><small>বিজয় না অভ্র · WPM · বাদ পড়লে কী হয়</small></span>
        <span class="ex-new">নতুন</span>
    </button>

    <template x-teleport="body">
        <div class="gd-overlay ex-overlay" x-show="open" x-cloak x-transition.opacity.duration.200ms @click.self="open = false" role="dialog" aria-modal="true" aria-label="সরকারি টাইপিং পরীক্ষার তথ্য">
            <div class="gd-modal ex-modal" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-6 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                <div class="gd-head ex-head">
                    <span style="font-size:1.7rem">📢</span>
                    <div><b>সরকারি টাইপিং পরীক্ষা: যা জানা জরুরি</b><small>পরীক্ষার আগে এই বিষয়গুলো জেনে নিন</small></div>
                    <button type="button" class="gd-x" @click="open = false" aria-label="বন্ধ করুন">&times;</button>
                </div>

                <div class="gd-body">
                    <div class="ex-card c1">
                        <div class="ex-num">১</div>
                        <div>
                            <h3>⌨️ বিজয় নাকি অভ্র — কোনটায় টাইপ করবেন?</h3>
                            <p>সরকারি পরীক্ষার সার্কুলারে সাধারণত নির্দিষ্ট করে বলা থাকে না যে বিজয় দিয়ে নাকি অভ্র দিয়ে টাইপ করতে হবে। তাই পরীক্ষার ল্যাবে <b>বিজয় ও অভ্র — দুটো অপশনই</b> থাকে।</p>
                            <p class="ex-tip">👉 আপনার যেটা সহজ লাগে, সেটাতেই ভালোভাবে দক্ষ হয়ে নিন। দুটোরই অনুশীলন TypingMoja-তে আছে।</p>
                        </div>
                    </div>

                    <div class="ex-card c2">
                        <div class="ex-num">২</div>
                        <div>
                            <h3>📏 WPM কীভাবে গোনা হয়?</h3>
                            <p>বেশিরভাগ পরীক্ষার ল্যাবে WPM <b>স্পেস সহ</b> গোনা হয়। হিসাব: <b>৫ স্ট্রোক = ১ শব্দ</b>।</p>
                            <div class="ex-formula">WPM = (সঠিক অক্ষর ÷ ৫) ÷ মিনিট</div>
                            <p class="ex-eg">উদাহরণ: ৩ মিনিটে স্পেস সহ ৩০০টি সঠিক অক্ষর টাইপ করলে<br>(৩০০ ÷ ৫) ÷ ৩ = <b>২০ WPM</b></p>
                            <p class="ex-tip">👉 TypingMoja-র প্রতিটি রেজাল্ট পেজে <b>"With Space / Without Space"</b> বাটন আছে। দুই ধরনেই নিজের গতি দেখে নিতে পারবেন।</p>
                        </div>
                    </div>

                    @if (\App\Models\Setting::strictSkip())
                        <div class="ex-card c3">
                            <div class="ex-num">৩</div>
                            <div>
                                <h3>⚠️ শব্দ বা লাইন বাদ পড়লে কী হয়?</h3>
                                <p>টাইপ পরীক্ষায় কোনো শব্দ বা লাইন বাদ পড়লে <b>এর পর থেকে সব শব্দ ভুল</b> ধরা হয়, আর সেগুলোর গতি গোনা হয় না। ফলে নির্ভুলতা অনেক কমে গিয়ে পরীক্ষায় ফেল হতে পারে।</p>
                                <p class="ex-tip">👉 TypingMoja-র টাইপ ল্যাব ও প্র্যাকটিসে এই নিয়মেই ফলাফল হয়। তাই অনুশীলনের সময় থেকেই একটাও শব্দ না ছেড়ে টাইপ করার অভ্যাস করুন।</p>
                            </div>
                        </div>
                    @endif

                    <div class="ex-note">ℹ️ প্রতিটি পরীক্ষার নিয়ম আলাদা হতে পারে। তাই আপনার পরীক্ষার সার্কুলার বা নির্দেশনাটা একবার দেখে নেবেন।</div>

                    <div class="gd-foot">
                        <button type="button" class="gd-btn ok" @click="open = false">বুঝেছি, প্র্যাকটিস করি 🚀</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
