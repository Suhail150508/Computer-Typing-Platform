<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedbackReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'new');

        $query = FeedbackReport::with('user')->latest();
        if (in_array($status, ['new', 'seen', 'resolved'], true)) {
            $query->where('status', $status);
        }

        $reports = $query->paginate(15)->withQueryString();
        $newCount = FeedbackReport::where('status', 'new')->count();

        return view('admin.feedback.index', compact('reports', 'status', 'newCount'));
    }

    public function update(Request $request, FeedbackReport $feedback): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:new,seen,resolved']]);
        $feedback->update($data);

        return back()->with('success', 'স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function destroy(FeedbackReport $feedback): RedirectResponse
    {
        if ($feedback->screenshot && is_file($f = public_path($feedback->screenshot))) {
            @unlink($f);
        }
        $feedback->delete();

        return back()->with('success', 'মেসেজটি মুছে ফেলা হয়েছে।');
    }
}
