<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\TypingAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('q', ''));
        $labId = $request->integer('lab') ?: null;

        $query = $user->typingAttempts()->with(['lab', 'practiceCard'])->latest();

        if ($labId) {
            $query->where('lab_id', $labId);
        }
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('language', 'like', $like)
                    ->orWhereHas('lab', fn ($l) => $l->where('name_bn', 'like', $like)->orWhere('name_en', 'like', $like))
                    ->orWhereHas('practiceCard', fn ($p) => $p->where('title', 'like', $like)->orWhere('tier', 'like', $like));
            });
        }

        $attempts = $query->paginate((int) config('typing.per_page'))->withQueryString();

        $total = $user->typingAttempts()->count();
        $passed = $user->typingAttempts()->where('result', 'pass')->count();
        $summary = [
            'total' => $total,
            'pass' => $passed,
            'fail' => $total - $passed,
            'pass_pct' => $total ? (int) round($passed / $total * 100) : 0,
            'fail_pct' => $total ? 100 - (int) round($passed / $total * 100) : 0,
        ];

        $labs = Lab::active()->get(['id', 'name_bn']);

        return view('results.index', compact('attempts', 'summary', 'labs', 'search', 'labId'));
    }

    public function show(Request $request, TypingAttempt $attempt): View
    {
        $this->authorizeOwner($request, $attempt);
        $attempt->load(['lab', 'practiceCard']);

        $meta = $attempt->diff_meta ?? [];

        // পাস করার ন্যূনতম WPM (স্মার্ট চেকার ও স্পিড চার্টের লক্ষ্যরেখার জন্য) — উৎস অনুযায়ী
        $minWpm = $attempt->isPractice() ? $attempt->practiceCard?->min_wpm : $attempt->lab?->min_wpm;
        if ($minWpm) {
            $meta['thresholds']['min_wpm'] = (int) $minWpm;
        }
        $original = preg_split('/\s+/u', trim((string) $attempt->original_text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $rest = array_slice($original, (int) ($meta['consumed'] ?? 0));

        $view = $request->ajax() ? 'results.partials.detail' : 'results.show';

        return view($view, compact('attempt', 'meta', 'rest'));
    }

    public function destroy(Request $request, TypingAttempt $attempt): RedirectResponse
    {
        $this->authorizeOwner($request, $attempt);
        $attempt->delete();

        return redirect()->route('results.index')->with('success', 'রেজাল্টটি মুছে ফেলা হয়েছে।');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->typingAttempts()->delete();

        return redirect()->route('results.index')->with('success', 'সব রেজাল্ট মুছে ফেলা হয়েছে।');
    }

    private function authorizeOwner(Request $request, TypingAttempt $attempt): void
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()->id, 403);
    }
}
