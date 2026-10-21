<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LabController extends Controller
{
    public function index(): View
    {
        $labs = Lab::withCount('passages')->orderBy('sort_order')->get();

        return view('admin.labs.index', compact('labs'));
    }

    public function create(): View
    {
        return view('admin.labs.form', ['lab' => new Lab()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name_bn']);

        Lab::create($data);

        return redirect()->route('admin.labs.index')->with('success', 'নতুন ল্যাব যোগ করা হয়েছে।');
    }

    public function edit(Lab $lab): View
    {
        return view('admin.labs.form', compact('lab'));
    }

    public function update(Request $request, Lab $lab): RedirectResponse
    {
        $lab->update($this->validated($request));

        return redirect()->route('admin.labs.index')->with('success', 'ল্যাব আপডেট হয়েছে।');
    }

    public function destroy(Lab $lab): RedirectResponse
    {
        if ($lab->passages()->exists()) {
            return back()->with('error', 'এই ল্যাবে প্যাসেজ থাকায় মোছা যাবে না। আগে প্যাসেজগুলো মুছুন বা নিষ্ক্রিয় করুন।');
        }

        $lab->delete();

        return redirect()->route('admin.labs.index')->with('success', 'ল্যাব মুছে ফেলা হয়েছে।');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name_bn' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'subtitle' => ['nullable', 'string', 'max:150'],
            'min_wpm' => ['required', 'integer', 'min:1', 'max:200'],
            'min_accuracy' => ['required', 'integer', 'min:1', 'max:100'],
            'min_duration_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'durations' => ['nullable', 'string', 'max:100', 'regex:/^\d{1,3}(,\d{1,3})*$/'],
        ], [
            'durations.regex' => 'সময়ের তালিকা সঠিক নয়।',
        ]);
        $data['durations'] = $this->cleanDurations($data['durations'] ?? null);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** "10,3,3,5,0,999" => "3,5,10" (১–১২০ মিনিট, ইউনিক, ছোট থেকে বড়); খালি হলে null (= ডিফল্ট) */
    private function cleanDurations(?string $raw): ?string
    {
        $list = collect(explode(',', (string) $raw))
            ->map(fn ($v) => (int) $v)->filter(fn ($v) => $v >= 1 && $v <= 120)
            ->unique()->sort()->values();

        return $list->isEmpty() ? null : $list->implode(',');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'lab';
        $slug = $base;
        $i = 1;
        while (Lab::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
