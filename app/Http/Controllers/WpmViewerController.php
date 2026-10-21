<?php

namespace App\Http\Controllers;

use App\Models\TypingAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WpmViewerController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $language = $request->query('lang') === 'english' ? 'english' : 'bangla';

        $base = TypingAttempt::query()->where('user_id', $user->id)->where('language', $language);

        $total = (with(clone $base))->count();
        $passed = (with(clone $base))->where('result', 'pass')->count();
        $bestWpm = (float) (with(clone $base))->max('wpm');
        $bestAccuracy = (float) (with(clone $base))->max('accuracy');
        $avgWpmRecent = (float) (with(clone $base))->latest()->limit(10)->get()->avg('wpm');

        // WPM-এর ধারা (সময়ের সাথে উন্নতি দেখানোর জন্য) — পুরনো থেকে নতুন ক্রমে সর্বশেষ ৩০টি
        $series = (with(clone $base))->orderByDesc('id')->limit(30)->get(['id', 'wpm', 'accuracy', 'created_at'])->sortBy('id')->values();

        // টানা কতদিন অনুশীলন করা হয়েছে (আজ/গতকাল থেকে পেছনে গুনে)
        $practiceDates = (with(clone $base))->selectRaw('DATE(created_at) as d')->distinct()->pluck('d')
            ->map(fn ($d) => (string) $d)->sort()->values();
        $streak = $this->currentStreak($practiceDates);

        $recent = (with(clone $base))->with(['lab', 'practiceCard'])->latest()->limit(12)->get();

        return view('wpm.index', [
            'language' => $language,
            'stats' => [
                'total' => $total,
                'passed' => $passed,
                'best_wpm' => $bestWpm,
                'best_accuracy' => $bestAccuracy,
                'avg_wpm_recent' => round($avgWpmRecent, 2),
                'streak' => $streak,
            ],
            'series' => $series,
            'recent' => $recent,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $dates  YYYY-MM-DD ফরম্যাটে, সাজানো
     */
    private function currentStreak($dates): int
    {
        if ($dates->isEmpty()) {
            return 0;
        }

        $set = $dates->flip();
        $cursor = now()->startOfDay();

        // আজকে অনুশীলন না থাকলে গতকাল থেকে গোনা শুরু (যাতে দিনের বাকি সময়ে streak শূন্য না দেখায়)
        if (! isset($set[$cursor->toDateString()])) {
            $cursor = $cursor->subDay();
        }

        $streak = 0;
        while (isset($set[$cursor->toDateString()])) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }
}
