<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TypingAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypingAttemptController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $result = $request->query('result'); // pass | fail
        $source = $request->query('source'); // lab | practice

        $query = TypingAttempt::with(['user', 'lab', 'practiceCard'])->latest();

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like));
        }
        if (in_array($result, ['pass', 'fail'], true)) {
            $query->where('result', $result);
        }
        if (in_array($source, ['lab', 'practice'], true)) {
            $query->where('source', $source);
        }

        $attempts = $query->paginate(20)->withQueryString();

        return view('admin.attempts.index', compact('attempts', 'search', 'result', 'source'));
    }

    public function show(TypingAttempt $attempt): View
    {
        $attempt->load(['user', 'lab', 'practiceCard']);
        $meta = $attempt->diff_meta ?? [];

        return view('admin.attempts.show', compact('attempt', 'meta'));
    }

    public function destroy(TypingAttempt $attempt): RedirectResponse
    {
        $attempt->delete();

        return redirect()->route('admin.attempts.index')->with('success', 'রেকর্ডটি মুছে ফেলা হয়েছে।');
    }
}
