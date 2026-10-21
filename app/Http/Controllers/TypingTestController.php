<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Passage;
use App\Models\TypingAttempt;
use App\Services\TypingEvaluator;
use App\Support\SpeedSamples;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TypingTestController extends Controller
{
    private const SESSION_KEY = 'typing.current';

    public function __construct(private readonly TypingEvaluator $evaluator)
    {
    }

    /** ধাপ ১: ল্যাব ও সময় নির্বাচন */
    public function setup(Request $request, Lab $lab): View
    {
        abort_unless($lab->is_active, 404);

        $language = $request->query('lang') === 'english' ? 'english' : 'bangla';
        $labs = Lab::active()->get();
        $durations = $lab->availableDurations();

        return view('typing.setup', compact('lab', 'labs', 'language', 'durations'));
    }

    /** ধাপ ২: টেস্ট শুরু — র‍্যান্ডম প্যাসেজ বেছে সেশনে রাখা হয় */
    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lab_id' => ['required', Rule::exists('labs', 'id')->where('is_active', true)],
            'language' => ['required', Rule::in(array_keys(config('typing.languages')))],
            'duration' => ['required', 'integer', 'min:1', 'max:120'],
        ], [
            'duration.required' => 'সময় নির্বাচন করুন।',
        ]);

        // সময়টি অবশ্যই এই ল্যাবের জন্য অ্যাডমিনের সেট করা তালিকার মধ্যে হতে হবে
        $lab = Lab::findOrFail($data['lab_id']);
        if (! in_array((int) $data['duration'], $lab->availableDurations(), true)) {
            return back()->withErrors(['duration' => 'সঠিক সময় নির্বাচন করুন।']);
        }

        if (! $request->user()->canPractice()) {
            return back()->with('error', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ। টেস্ট দিতে সাবস্ক্রিপশন রিনিউ করুন।');
        }

        $passage = Passage::query()
            ->where('lab_id', $data['lab_id'])
            ->where('language', $data['language'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $passage) {
            return back()->with('error', 'এই ল্যাবের জন্য এখনো কোনো প্যাসেজ যোগ করা হয়নি।');
        }

        $request->session()->put(self::SESSION_KEY, [
            'token' => (string) Str::uuid(),
            'passage_id' => $passage->id,
            'lab_id' => (int) $data['lab_id'],
            'language' => $data['language'],
            'duration_seconds' => (int) $data['duration'] * 60,
            'started_at' => now()->timestamp,
        ]);

        \App\Models\ActivityLog::record('test_start', "টাইপ ল্যাব শুরু: {$lab->name_bn} ({$data['language']}, {$data['duration']} মিনিট)", ['lab_id' => $lab->id], $request);

        return redirect()->route('typing.test');
    }

    /** ধাপ ৩: টাইপিং পেজ */
    public function test(Request $request): View|RedirectResponse
    {
        $current = $request->session()->get(self::SESSION_KEY);
        if (! $current) {
            return redirect()->route('dashboard');
        }

        $passage = Passage::with('lab')->find($current['passage_id']);
        if (! $passage) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('dashboard')->with('error', 'প্যাসেজটি পাওয়া যায়নি, আবার চেষ্টা করুন।');
        }

        $paragraphs = collect(preg_split('/\R{2,}/u', trim($passage->content)) ?: [])
            ->map(fn (string $p) => preg_split('/\s+/u', trim($p), -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->filter()
            ->values();

        return view('typing.test', [
            'passage' => $passage,
            'sourceLabel' => $passage->lab?->name_bn,
            'submitRoute' => route('typing.submit'),
            'paragraphs' => $paragraphs,
            'token' => $current['token'],
            'duration' => $current['duration_seconds'],
            'language' => $current['language'],
        ]);
    }

    /** ধাপ ৪: সাবমিট — সার্ভারেই হিসাব হয়, ক্লায়েন্টের হিসাব বিশ্বাস করা হয় না */
    public function submit(Request $request): RedirectResponse
    {
        $current = $request->session()->get(self::SESSION_KEY);
        if (! $current) {
            return redirect()->route('dashboard')->with('error', 'টেস্ট সেশন পাওয়া যায়নি বা আগেই জমা হয়ে গেছে।');
        }

        $data = $request->validate([
            'token' => ['required', 'string'],
            'typed_text' => ['nullable', 'string', 'max:60000'],
            'elapsed' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'speed_samples' => ['nullable', 'string', 'max:40000'],
        ]);

        if (! hash_equals($current['token'], $data['token'])) {
            return redirect()->route('dashboard')->with('error', 'অবৈধ টেস্ট সেশন।');
        }

        $request->session()->forget(self::SESSION_KEY);

        $typed = (string) ($data['typed_text'] ?? '');
        if (trim($typed) === '') {
            return redirect()->route('dashboard')->with('error', 'আপনি কিছুই টাইপ করেননি, তাই ফলাফল সংরক্ষণ হয়নি।');
        }

        $passage = Passage::with('lab')->findOrFail($current['passage_id']);
        $lab = $passage->lab;
        $duration = (int) $current['duration_seconds'];

        // ক্লায়েন্টের সময় সার্ভারের বাস্তব সময়ের চেয়ে বেশি হতে পারে না; আর নির্ধারিত সময়ের বেশিও নয়
        $serverElapsed = max(1, now()->timestamp - (int) $current['started_at']);
        $timeTaken = min((int) ($data['elapsed'] ?? $serverElapsed), $serverElapsed, $duration);
        $timeTaken = max(1, $timeTaken);

        $result = $this->evaluator->evaluate(
            $passage->content,
            $typed,
            $timeTaken,
            $duration,
            (int) $lab->min_wpm,
            (int) $lab->min_accuracy,
            (int) $lab->min_duration_percent,
            \App\Models\Setting::strictSkip()
        );

        $attempt = TypingAttempt::create($result + [
            'user_id' => $request->user()->id,
            'passage_id' => $passage->id,
            'lab_id' => $lab->id,
            'language' => $passage->language,
            'duration_seconds' => $duration,
            'typed_text' => $typed,
            'original_text' => $passage->content,
            'speed_samples' => SpeedSamples::sanitize($data['speed_samples'] ?? null, $timeTaken),
        ]);

        \App\Models\ActivityLog::record('test_submit', "টাইপ ল্যাব: {$lab->name_bn} — {$attempt->wpm} WPM, নির্ভুলতা {$attempt->accuracy}%, ".($attempt->isPass() ? 'PASS' : 'FAIL'), ['attempt_id' => $attempt->id], $request);

        return redirect()->route('results.show', $attempt)->with('just_finished', true);
    }
}
