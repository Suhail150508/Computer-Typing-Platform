<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\PracticeCard;
use App\Models\TypingAttempt;
use App\Services\PracticeProgressService;
use App\Services\TypingEvaluator;
use App\Support\SpeedSamples;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PracticeController extends Controller
{
    private const SESSION_KEY = 'practice.current';

    public function __construct(
        private readonly PracticeProgressService $progress,
        private readonly TypingEvaluator $evaluator
    ) {
    }

    /** টাইপিং শুরু হাব: ভাষা (বাংলা/English) + বেসিক/অ্যাডভান্স/হার্ড — কার্ড, লক স্ট্যাটাস, প্রগ্রেস */
    public function index(Request $request): View
    {
        $language = $request->query('lang') === 'english' ? 'english' : 'bangla';
        $tier = in_array($request->query('tier'), PracticeCard::TIERS, true) ? $request->query('tier') : 'basic';

        $board = $this->progress->board($request->user(), $language);
        $allDone = $this->progress->allTiersCompleted($request->user(), $language);

        return view('practice.index', compact('board', 'tier', 'language', 'allDone'));
    }

    /** লক করা কার্ডে ক্লিক করলে তথ্য দেখাবে (AJAX) — খেলার সুযোগ নেই */
    public function preview(Request $request, PracticeCard $card): JsonResponse
    {
        $unlocked = $this->progress->isTierUnlocked($request->user(), $card->tier, $card->language);
        $passedInPrev = $this->progress->passedCountsByTier($request->user(), $card->language);

        return response()->json([
            'title' => $card->title,
            'icon' => $card->icon,
            'description' => $card->description,
            'tier' => $card->tier,
            'tier_label' => $card->tierLabel(),
            'language' => $card->language,
            'duration_seconds' => $card->duration_seconds,
            'min_wpm' => $card->min_wpm,
            'min_accuracy' => $card->min_accuracy,
            'unlocked' => $unlocked,
            'requirement' => $unlocked ? null : $this->lockMessage($card->tier, $passedInPrev),
        ]);
    }

    private function lockMessage(string $tier, array $passedInPrev): ?string
    {
        $threshold = Setting::typing('practice_unlock_threshold');

        return match ($tier) {
            'advance' => "আনলক করতে বেসিকের কমপক্ষে {$threshold}টি কার্ড পাস করতে হবে (এখন পাস: {$passedInPrev['basic']})",
            'hard' => "আনলক করতে অ্যাডভান্সের কমপক্ষে {$threshold}টি কার্ড পাস করতে হবে (এখন পাস: {$passedInPrev['advance']})",
            default => null,
        };
    }

    /** কার্ডে ক্লিক করে টেস্ট শুরু — টায়ার লক থাকলে ফিরিয়ে দেওয়া হবে */
    public function start(Request $request, PracticeCard $card): RedirectResponse
    {
        abort_unless($card->is_active, 404);

        if (! $this->progress->isTierUnlocked($request->user(), $card->tier, $card->language)) {
            return redirect()->route('practice.index', ['tier' => $card->tier, 'lang' => $card->language])
                ->with('error', 'এই কার্ডটি এখনো লক করা। আগের ধাপ সম্পন্ন করুন।');
        }

        if (! $request->user()->canPractice()) {
            return redirect()->route('practice.index')->with('error', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ। টেস্ট দিতে সাবস্ক্রিপশন রিনিউ করুন।');
        }

        $request->session()->put(self::SESSION_KEY, [
            'token' => (string) Str::uuid(),
            'card_id' => $card->id,
            'tier' => $card->tier,
            'language' => $card->language,
            'duration_seconds' => $card->duration_seconds,
            'started_at' => now()->timestamp,
        ]);

        \App\Models\ActivityLog::record('test_start', "টাইপিং শুরু কার্ড #{$card->id} ({$card->tier}, {$card->language})", ['card_id' => $card->id], $request);

        return redirect()->route('practice.test');
    }

    public function test(Request $request): View|RedirectResponse
    {
        $current = $request->session()->get(self::SESSION_KEY);
        if (! $current) {
            return redirect()->route('practice.index');
        }

        $card = PracticeCard::find($current['card_id']);
        if (! $card) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('practice.index')->with('error', 'কার্ডটি পাওয়া যায়নি, আবার চেষ্টা করুন।');
        }

        $paragraphs = collect(preg_split('/\R{2,}/u', trim($card->content)) ?: [])
            ->map(fn (string $p) => preg_split('/\s+/u', trim($p), -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->filter()
            ->values();

        return view('typing.test', [
            'passage' => $card,
            'sourceLabel' => 'টাইপিং শুরু · '.$card->tierLabel().' #'.$card->order,
            'submitRoute' => route('practice.submit'),
            'paragraphs' => $paragraphs,
            'token' => $current['token'],
            'duration' => $current['duration_seconds'],
            'language' => $card->language,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $current = $request->session()->get(self::SESSION_KEY);
        if (! $current) {
            return redirect()->route('practice.index')->with('error', 'টেস্ট সেশন পাওয়া যায়নি বা আগেই জমা হয়ে গেছে।');
        }

        $data = $request->validate([
            'token' => ['required', 'string'],
            'typed_text' => ['nullable', 'string', 'max:60000'],
            'elapsed' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'speed_samples' => ['nullable', 'string', 'max:40000'],
        ]);

        if (! hash_equals($current['token'], $data['token'])) {
            return redirect()->route('practice.index')->with('error', 'অবৈধ টেস্ট সেশন।');
        }

        $request->session()->forget(self::SESSION_KEY);

        $typed = (string) ($data['typed_text'] ?? '');
        if (trim($typed) === '') {
            return redirect()->route('practice.index', ['tier' => $current['tier'], 'lang' => $current['language']])
                ->with('error', 'আপনি কিছুই টাইপ করেননি, তাই ফলাফল সংরক্ষণ হয়নি।');
        }

        $card = PracticeCard::findOrFail($current['card_id']);
        $duration = (int) $current['duration_seconds'];
        $language = $card->language;

        $serverElapsed = max(1, now()->timestamp - (int) $current['started_at']);
        $timeTaken = min((int) ($data['elapsed'] ?? $serverElapsed), $serverElapsed, $duration);
        $timeTaken = max(1, $timeTaken);

        $wasUnlockedBefore = $this->progress->unlockedTiers($request->user(), $language);

        $result = $this->evaluator->evaluate(
            $card->content,
            $typed,
            $timeTaken,
            $duration,
            (int) $card->min_wpm,
            (int) $card->min_accuracy,
            (int) $card->min_duration_percent,
            \App\Models\Setting::strictSkip()
        );

        $attempt = TypingAttempt::create($result + [
            'user_id' => $request->user()->id,
            'practice_card_id' => $card->id,
            'source' => 'practice',
            'language' => $language,
            'duration_seconds' => $duration,
            'typed_text' => $typed,
            'original_text' => $card->content,
            'speed_samples' => SpeedSamples::sanitize($data['speed_samples'] ?? null, $timeTaken),
        ]);

        \App\Models\ActivityLog::record('test_submit', "টাইপিং শুরু কার্ড #{$card->id}: {$attempt->wpm} WPM, নির্ভুলতা {$attempt->accuracy}%, ".($attempt->isPass() ? 'PASS' : 'FAIL'), ['attempt_id' => $attempt->id], $request);

        $wasUnlockedAfter = $this->progress->unlockedTiers($request->user(), $language);
        $justUnlocked = null;
        foreach (['advance', 'hard'] as $tier) {
            if (! $wasUnlockedBefore[$tier] && $wasUnlockedAfter[$tier]) {
                $justUnlocked = $tier;
            }
        }

        return redirect()->route('results.show', $attempt)
            ->with('just_finished', true)
            ->with('just_unlocked', $justUnlocked);
    }
}
