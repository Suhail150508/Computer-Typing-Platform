<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SheetExercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SheetExerciseController extends Controller
{
    public function index(Request $request): View
    {
        $language = in_array($request->query('language'), ['bangla', 'english'], true) ? $request->query('language') : null;

        $exercises = SheetExercise::query()
            ->when($language, fn ($q, $l) => $q->where('language', $l))
            ->orderBy('language')->orderBy('order')->orderBy('id')
            ->paginate(20)->withQueryString();

        return view('admin.sheet-exercises.index', compact('exercises', 'language'));
    }

    public function create(): View
    {
        return view('admin.sheet-exercises.form', ['exercise' => new SheetExercise()]);
    }

    public function store(Request $request): RedirectResponse
    {
        SheetExercise::create($this->validated($request));

        return redirect()->route('admin.sheet-exercises.index')->with('success', 'নতুন শীট যোগ করা হয়েছে।');
    }

    public function edit(SheetExercise $sheetExercise): View
    {
        return view('admin.sheet-exercises.form', ['exercise' => $sheetExercise]);
    }

    public function update(Request $request, SheetExercise $sheetExercise): RedirectResponse
    {
        $sheetExercise->update($this->validated($request));

        return redirect()->route('admin.sheet-exercises.index')->with('success', 'শীট আপডেট হয়েছে।');
    }

    public function destroy(SheetExercise $sheetExercise): RedirectResponse
    {
        $sheetExercise->delete();

        return redirect()->route('admin.sheet-exercises.index')->with('success', 'শীট মুছে ফেলা হয়েছে (ইউজারদের আগের রেজাল্ট থেকে যাবে)।');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:200'],
            'icon' => ['nullable', 'string', 'max:10'],
            'language' => ['required', Rule::in(['bangla', 'english'])],
            'order' => ['required', 'integer', 'min:0', 'max:999'],
            'table_data' => ['required', 'string', 'max:60000'],
            'duration_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
            'min_wpm' => ['required', 'integer', 'min:1', 'max:300'],
            'min_accuracy' => ['required', 'integer', 'min:1', 'max:100'],
            'min_duration_percent' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'table_data.required' => 'শীটের ডাটা দিন (Excel থেকে কপি করে পেস্ট করতে পারেন)।',
        ]);

        $parsed = SheetExercise::parseTable($data['table_data'], $request->boolean('has_header'));
        $rows = $parsed['rows'];

        if ($rows === []) {
            throw ValidationException::withMessages(['table_data' => 'কমপক্ষে একটি ডাটা সারি দিন।']);
        }
        if (count($rows) > SheetExercise::MAX_ROWS) {
            throw ValidationException::withMessages(['table_data' => 'সর্বোচ্চ '.SheetExercise::MAX_ROWS.'টি সারি দেওয়া যাবে।']);
        }
        if (count($rows[0]) > SheetExercise::MAX_COLS) {
            throw ValidationException::withMessages(['table_data' => 'সর্বোচ্চ '.SheetExercise::MAX_COLS.'টি কলাম দেওয়া যাবে।']);
        }
        foreach (array_merge($parsed['headers'] ? [$parsed['headers']] : [], $rows) as $row) {
            foreach ($row as $cell) {
                if (mb_strlen($cell) > SheetExercise::MAX_CELL_LENGTH) {
                    throw ValidationException::withMessages(['table_data' => 'একটি ঘরে সর্বোচ্চ '.SheetExercise::MAX_CELL_LENGTH.' অক্ষর দেওয়া যাবে: "'.mb_substr($cell, 0, 20).'…"']);
                }
            }
        }

        // কমপক্ষে একটি ঘরে লেখা থাকতে হবে, নইলে টাইপ করার কিছুই নেই
        if (SheetExercise::flatten($rows) === '') {
            throw ValidationException::withMessages(['table_data' => 'ঘরগুলো সবই খালি।']);
        }

        unset($data['table_data']);
        $data['headers'] = $parsed['headers'];
        $data['rows'] = $rows;
        $data['icon'] = $data['icon'] ?: '📊';
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
