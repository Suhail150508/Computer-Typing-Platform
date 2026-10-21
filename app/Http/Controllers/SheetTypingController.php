<?php

namespace App\Http\Controllers;

use App\Models\SheetExercise;
use App\Models\TypingAttempt;
use App\Services\TypingEvaluator;
use App\Support\SpeedSamples;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * শীট টাইপিং: স্প্রেডশিটের মতো টেবিল দেখে ঘরে ঘরে টাইপ করা।
 * ফলাফল হিসাব হয় সার্ভারে, বাকি সব টেস্টের মতো একই TypingEvaluator দিয়ে (সব ঘর সারি-ক্রমে জুড়ে)।
 */
class SheetTypingController extends Controller
{
    private const SESSION_KEY = 'sheet.current';

    public function __construct(private readonly TypingEvaluator $evaluator)
    {
    }

    public function index(Request $request): View
    {
        $language = $request->query('lang') === 'english' ? 'english' : 'bangla';

        $exercises = SheetExercise::active()->where('language', $language)->get();

        // প্রতিটি অনুশীলনে ইউজারের সেরা ফলাফল
        $best = $request->user()->typingAttempts()
            ->whereNotNull('sheet_exercise_id')
            ->get(['sheet_exercise_id', 'wpm', 'accuracy', 'result'])
            ->groupBy('sheet_exercise_id')
            ->map(fn ($rows) => [
                'tries' => $rows->count(),
                'wpm' => (float) $rows->max('wpm'),
                'accuracy' => (float) $rows->max('accuracy'),
                'passed' => $rows->contains('result', 'pass'),
            ]);

        return view('sheet.index', compact('exercises', 'language', 'best'));
    }

    public function start(Request $request, SheetExercise $exercise): RedirectResponse
    {
        abort_unless($exercise->is_active, 404);

        if (! $request->user()->canPractice()) {
            return redirect()->route('sheet.index')->with('error', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ। টেস্ট দিতে সাবস্ক্রিপশন রিনিউ করুন।');
        }

        $request->session()->put(self::SESSION_KEY, [
            'token' => (string) Str::uuid(),
            'exercise_id' => $exercise->id,
            'duration_seconds' => (int) $exercise->duration_seconds,
            'started_at' => now()->timestamp,
        ]);

        return redirect()->route('sheet.test');
    }

    public function test(Request $request): View|RedirectResponse
    {
        $current = $request->session()->get(self::SESSION_KEY);
        if (! $current) {
            return redirect()->route('sheet.index');
        }

        $exercise = SheetExercise::find($current['exercise_id']);
        if (! $exercise) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('sheet.index')->with('error', 'শীটটি পাওয়া যায়নি, আবার চেষ্টা করুন।');
        }

        return view('sheet.test', [
            'exercise' => $exercise,
            'token' => $current['token'],
            'duration' => (int) $current['duration_seconds'],
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $current = $request->session()->get(self::SESSION_KEY);
        if (! $current) {
            return redirect()->route('sheet.index')->with('error', 'টেস্ট সেশন পাওয়া যায়নি বা আগেই জমা হয়ে গেছে।');
        }

        $data = $request->validate([
            'token' => ['required', 'string'],
            'cells' => ['nullable', 'string', 'max:100000'],
            'elapsed' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'speed_samples' => ['nullable', 'string', 'max:40000'],
        ]);

        if (! hash_equals($current['token'], $data['token'])) {
            return redirect()->route('sheet.index')->with('error', 'অবৈধ টেস্ট সেশন।');
        }

        $request->session()->forget(self::SESSION_KEY);

        $exercise = SheetExercise::findOrFail($current['exercise_id']);

        // ঘরের লেখা: [[সারি১-ঘর১, সারি১-ঘর২, ...], ...] — শীটের আসল মাপের বাইরের কিছু নেওয়া হয় না
        $decoded = json_decode((string) ($data['cells'] ?? ''), true);
        $cells = [];
        if (is_array($decoded)) {
            foreach (array_slice(array_values($decoded), 0, $exercise->rowCount()) as $row) {
                $cells[] = array_map(
                    fn ($c) => mb_substr(is_scalar($c) ? (string) $c : '', 0, SheetExercise::MAX_CELL_LENGTH * 2),
                    array_slice(array_values((array) $row), 0, $exercise->colCount())
                );
            }
        }

        $typed = SheetExercise::flatten($cells);
        if ($typed === '') {
            return redirect()->route('sheet.index', ['lang' => $exercise->language])
                ->with('error', 'আপনি কিছুই টাইপ করেননি, তাই ফলাফল সংরক্ষণ হয়নি।');
        }

        $duration = (int) $current['duration_seconds'];
        $serverElapsed = max(1, now()->timestamp - (int) $current['started_at']);
        $timeTaken = max(1, min((int) ($data['elapsed'] ?? $serverElapsed), $serverElapsed, $duration));

        $original = $exercise->flatText();

        $result = $this->evaluator->evaluate(
            $original,
            $typed,
            $timeTaken,
            $duration,
            (int) $exercise->min_wpm,
            (int) $exercise->min_accuracy,
            (int) $exercise->min_duration_percent
        );

        $attempt = TypingAttempt::create($result + [
            'user_id' => $request->user()->id,
            'sheet_exercise_id' => $exercise->id,
            'source' => 'lab',
            'language' => $exercise->language,
            'duration_seconds' => $duration,
            'typed_text' => $typed,
            'original_text' => $original,
            'speed_samples' => SpeedSamples::sanitize($data['speed_samples'] ?? null, $timeTaken),
        ]);

        return redirect()->route('results.show', $attempt)->with('just_finished', true);
    }
}
