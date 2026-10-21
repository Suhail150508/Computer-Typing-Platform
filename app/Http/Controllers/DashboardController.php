<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $labs = Lab::active()->withCount([
            'passages as bangla_count' => fn ($q) => $q->where('is_active', true)->where('language', 'bangla'),
            'passages as english_count' => fn ($q) => $q->where('is_active', true)->where('language', 'english'),
        ])->get();

        $stats = [
            'attempts' => $user->typingAttempts()->count(),
            'passed' => $user->typingAttempts()->where('result', 'pass')->count(),
            'best_wpm' => (float) $user->typingAttempts()->max('wpm'),
        ];

        $last = $user->typingAttempts()->with(['lab', 'practiceCard'])->latest('id')->first();

        return view('dashboard', compact('labs', 'stats', 'last'));
    }
}
