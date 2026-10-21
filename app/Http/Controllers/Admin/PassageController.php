<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\Passage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PassageController extends Controller
{
    public function index(Request $request): View
    {
        $labId = $request->integer('lab') ?: null;

        $query = Passage::with('lab')->latest();
        if ($labId) {
            $query->where('lab_id', $labId);
        }

        $passages = $query->paginate(15)->withQueryString();
        $labs = Lab::orderBy('sort_order')->get();

        return view('admin.passages.index', compact('passages', 'labs', 'labId'));
    }

    public function create(): View
    {
        $labs = Lab::orderBy('sort_order')->get();

        return view('admin.passages.form', ['passage' => new Passage(), 'labs' => $labs]);
    }

    public function store(Request $request): RedirectResponse
    {
        Passage::create($this->validated($request));

        return redirect()->route('admin.passages.index')->with('success', 'নতুন প্যাসেজ যোগ করা হয়েছে।');
    }

    public function edit(Passage $passage): View
    {
        $labs = Lab::orderBy('sort_order')->get();

        return view('admin.passages.form', compact('passage', 'labs'));
    }

    public function update(Request $request, Passage $passage): RedirectResponse
    {
        $passage->update($this->validated($request));

        return redirect()->route('admin.passages.index')->with('success', 'প্যাসেজ আপডেট হয়েছে।');
    }

    public function destroy(Passage $passage): RedirectResponse
    {
        $passage->delete();

        return redirect()->route('admin.passages.index')->with('success', 'প্যাসেজ মুছে ফেলা হয়েছে।');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'lab_id' => ['required', 'exists:labs,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'language' => ['required', Rule::in(['bangla', 'english'])],
            'content' => ['required', 'string', 'min:10', 'max:20000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
