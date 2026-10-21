<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        Cache::forget('activity_log_ready'); // অ্যাডমিন পেজ খুললেই টেবিলের অবস্থা নতুন করে যাচাই হয়
        $diag = $this->diagnose();

        if (! ActivityLog::ready()) {
            return view('admin.activity.index', ['ready' => false, 'diag' => $diag]);
        }

        // দিনে একবার ৯০ দিনের পুরনো লগ মুছে ফেলা
        if (Cache::add('activity_pruned_today', 1, 86400)) {
            ActivityLog::where('created_at', '<', now()->subDays(90))->delete();
        }

        $type = $request->query('type');
        $userId = $request->query('user');
        $q = trim((string) $request->query('q', ''));
        $date = $request->query('date');

        $query = ActivityLog::with('user:id,name,email')->latest('id');
        if ($type && isset(ActivityLog::TYPES[$type])) {
            $query->where('type', $type);
        }
        if ($userId && ctype_digit((string) $userId)) {
            $query->where('user_id', (int) $userId);
        }
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(function ($w) use ($like) {
                $w->where('label', 'like', $like)->orWhere('ip', 'like', $like)
                    ->orWhereIn('user_id', User::where('name', 'like', $like)->orWhere('email', 'like', $like)->pluck('id'));
            });
        }
        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $day = now()->parse($date)->startOfDay();
            $query->whereBetween('created_at', [$day, $day->copy()->endOfDay()]);
        }

        $logs = $query->paginate(40)->withQueryString();

        // ---- সারাংশ ----
        $todayStart = now()->startOfDay();
        $today = $this->summary($todayStart, now());
        $online = $this->summary(now()->subMinutes(5), now());
        $yesterday = $this->summary($todayStart->copy()->subDay(), $todayStart);

        // ---- ১৪ দিনের চার্ট ----
        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $s = $todayStart->copy()->subDays($i);
            $sum = $this->summary($s, $s->copy()->endOfDay());
            $days[] = ['label' => $s->format('d M'), 'visitors' => $sum['visitors'], 'views' => $sum['views']];
        }
        $maxVisitors = max(1, collect($days)->max('visitors'));

        // ---- আজকের জনপ্রিয় পেজ ও সক্রিয় ইউজার ----
        $topPages = ActivityLog::where('type', 'page')->where('created_at', '>=', $todayStart)
            ->select('label', DB::raw('COUNT(*) as c'))->groupBy('label')->orderByDesc('c')->limit(8)->get();

        $recentUsers = ActivityLog::with('user:id,name,email')->whereNotNull('user_id')->where('created_at', '>=', now()->subMinutes(30))
            ->latest('id')->limit(200)->get()->unique('user_id')->take(8);

        return view('admin.activity.index', [
            'ready' => true, 'diag' => $diag, 'logs' => $logs, 'today' => $today, 'online' => $online, 'yesterday' => $yesterday,
            'days' => $days, 'maxVisitors' => $maxVisitors, 'topPages' => $topPages, 'recentUsers' => $recentUsers,
            'filters' => compact('type', 'userId', 'q', 'date'),
            'filterUser' => ($userId && ctype_digit((string) $userId)) ? User::find((int) $userId) : null,
        ]);
    }

    /** একটা পরীক্ষামূলক লগ সরাসরি লিখে দেখায় (এরর লুকানো ছাড়া) */
    public function test(Request $request): RedirectResponse
    {
        try {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'visitor' => ActivityLog::visitorId($request),
                'type' => 'page',
                'label' => 'পরীক্ষামূলক লগ (ডায়াগনস্টিক) — বাংলা লেখা ঠিকভাবে সেভ হচ্ছে',
                'path' => '/admin/activity',
                'ip' => $request->ip(),
                'device' => ActivityLog::device((string) $request->userAgent()),
                'created_at' => now(),
            ]);
            Cache::forget('activity_log_last_error');

            return back()->with('success', '✅ পরীক্ষামূলক লগ সফলভাবে লেখা হয়েছে — লগ ঠিকমতো কাজ করছে।');
        } catch (\Throwable $e) {
            return back()->with('error', '❌ লগ লেখা যায়নি: '.mb_substr($e->getMessage(), 0, 600));
        }
    }

    /** @return array<string, mixed> */
    private function diagnose(): array
    {
        $d = ['db' => '—', 'table' => false, 'charset' => null, 'rows' => null, 'middleware' => false, 'last_error' => Cache::get('activity_log_last_error'), 'cache' => config('cache.default')];
        try {
            $d['db'] = DB::connection()->getDatabaseName();
            $d['table'] = \Illuminate\Support\Facades\Schema::hasTable('activity_logs');
            if ($d['table']) {
                $d['rows'] = ActivityLog::count();
                $row = DB::selectOne('SELECT CCSA.character_set_name AS cs FROM information_schema.TABLES T JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA ON CCSA.collation_name = T.table_collation WHERE T.table_schema = ? AND T.table_name = ?', [$d['db'], 'activity_logs']);
                $d['charset'] = $row->cs ?? null;
            }
        } catch (\Throwable $e) {
            $d['error'] = $e->getMessage();
        }
        try {
            $d['middleware'] = in_array(\App\Http\Middleware\LogPageViews::class, app('router')->getMiddlewareGroups()['web'] ?? [], true);
        } catch (\Throwable $e) {
        }

        return $d;
    }

    public function prune(Request $request): RedirectResponse
    {
        $days = max(1, min(365, (int) $request->input('days', 30)));
        $n = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "{$days} দিনের পুরনো {$n}টি লগ মুছে ফেলা হয়েছে।");
    }

    /** @return array{visitors:int,users:int,guests:int,views:int,tests:int,registers:int} */
    private function summary($from, $to): array
    {
        $base = fn () => ActivityLog::whereBetween('created_at', [$from, $to]);

        $users = (clone $base())->whereNotNull('user_id')->distinct()->count('user_id');
        $guests = (clone $base())->whereNull('user_id')->distinct()->count('visitor');

        return [
            'visitors' => $users + $guests,
            'users' => $users,
            'guests' => $guests,
            'views' => (clone $base())->where('type', 'page')->count(),
            'tests' => (clone $base())->where('type', 'test_submit')->count(),
            'registers' => (clone $base())->where('type', 'register')->count(),
        ];
    }
}
