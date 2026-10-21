<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::withCount('purchases')->orderBy('sort_order')->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.subscription-plans.form', ['plan' => new SubscriptionPlan()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        SubscriptionPlan::create($data);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'নতুন প্ল্যান যোগ করা হয়েছে।');
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        return view('admin.subscription-plans.form', ['plan' => $subscriptionPlan]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->update($this->validated($request));

        return redirect()->route('admin.subscription-plans.index')->with('success', 'প্ল্যান আপডেট হয়েছে।');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->purchases()->exists()) {
            return back()->with('error', 'এই প্ল্যানে পূর্বের ক্রয়-রেকর্ড থাকায় মোছা যাবে না। চাইলে নিষ্ক্রিয় করে দিন।');
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')->with('success', 'প্ল্যান মুছে ফেলা হয়েছে।');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'features_text' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // প্রতি লাইনে একটা ফিচার লিখলে সেটাকে JSON অ্যারেতে রূপান্তর করি
        $data['features'] = collect(explode("\n", (string) ($data['features_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        unset($data['features_text']);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $i = 1;
        while (SubscriptionPlan::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
