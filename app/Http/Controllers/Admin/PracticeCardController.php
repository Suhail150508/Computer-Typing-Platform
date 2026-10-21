<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PracticeCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PracticeCardController extends Controller
{
    public function index(Request $request): View
    {
        $tier = $request->query('tier');
        $language = $request->query('language');

        $query = PracticeCard::query()->orderBy('tier')->orderBy('language')->orderBy('order');
        if ($tier) {
            $query->where('tier', $tier);
        }
        if ($language) {
            $query->where('language', $language);
        }

        $cards = $query->paginate(20)->withQueryString();

        return view('admin.practice-cards.index', compact('cards', 'tier', 'language'));
    }

    public function create(): View
    {
        return view('admin.practice-cards.form', ['card' => new PracticeCard()]);
    }

    public function store(Request $request): RedirectResponse
    {
        PracticeCard::create($this->validated($request));

        return redirect()->route('admin.practice-cards.index')->with('success', 'নতুন কার্ড যোগ করা হয়েছে।');
    }

    public function edit(PracticeCard $practiceCard): View
    {
        return view('admin.practice-cards.form', ['card' => $practiceCard]);
    }

    public function update(Request $request, PracticeCard $practiceCard): RedirectResponse
    {
        $practiceCard->update($this->validated($request));

        return redirect()->route('admin.practice-cards.index')->with('success', 'কার্ড আপডেট হয়েছে।');
    }

    public function destroy(PracticeCard $practiceCard): RedirectResponse
    {
        $practiceCard->delete();

        return redirect()->route('admin.practice-cards.index')->with('success', 'কার্ড মুছে ফেলা হয়েছে।');
    }

    /** একসাথে অনেক কার্ডের সময় বদলানো (টায়ার/ভাষা অনুযায়ী ফিল্টার করে) */
    public function bulkDuration(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tier' => ['nullable', Rule::in(PracticeCard::TIERS)],
            'language' => ['nullable', Rule::in(['bangla', 'english'])],
            'duration_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
        ], [
            'duration_seconds.required' => 'সময় বেছে নিন।',
        ]);

        $count = PracticeCard::query()
            ->when($data['tier'] ?? null, fn ($q, $t) => $q->where('tier', $t))
            ->when($data['language'] ?? null, fn ($q, $l) => $q->where('language', $l))
            ->update(['duration_seconds' => (int) $data['duration_seconds']]);

        return redirect()->route('admin.practice-cards.index', array_filter([
            'tier' => $data['tier'] ?? null, 'language' => $data['language'] ?? null,
        ]))->with('success', "{$count}টি কার্ডের সময় ".\App\Support\Bn::minutes((int) $data['duration_seconds']).' মিনিট করা হয়েছে।');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'tier' => ['required', Rule::in(PracticeCard::TIERS)],
            'language' => ['required', Rule::in(['bangla', 'english'])],
            'order' => ['required', 'integer', 'min:1', 'max:999'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:200'],
            'icon' => ['nullable', 'string', 'max:10'],
            'duration_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
            'min_wpm' => ['required', 'integer', 'min:1', 'max:300'],
            'min_accuracy' => ['required', 'integer', 'min:1', 'max:100'],
            'min_duration_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'content' => ['required', 'string', 'min:10', 'max:30000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['icon'] = $data['icon'] ?: '⌨️';

        return $data;
    }
}
