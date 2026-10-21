<?php

namespace App\Http\Controllers;

use App\Support\KeyLayouts;
use App\Support\Lessons;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function index(Request $request): View
    {
        $lang = in_array($request->query('lang'), ['english', 'bijoy', 'avro'], true) ? $request->query('lang') : 'english';

        return view('learn.index', [
            'lang' => $lang,
            'lessons' => Lessons::forLang($lang),
            'doneDb' => $this->doneIds($request),
        ]);
    }

    public function show(Request $request, string $lesson): View
    {
        $data = Lessons::find($lesson);
        abort_if($data === null, 404);

        $siblings = Lessons::forLang($data['lang']);
        $idx = array_search($data['id'], array_column($siblings, 'id'), true);
        $next = $siblings[$idx + 1] ?? null;

        if ($data['mode'] === 'keys') {
            return view('learn.trainer', [
                'lesson' => $data,
                'next' => $next,
                'layout' => $data['lang'] === 'bijoy' ? KeyLayouts::bijoy() : KeyLayouts::plain(),
                'fingers' => KeyLayouts::fingers(),
                'types' => KeyLayouts::TYPES,
            ]);
        }

        return view('learn.show', ['lesson' => $data, 'next' => $next, 'total' => count($siblings)]);
    }

    /** পাঠ শেষ হলে অগ্রগতি জমা (টেবিল না থাকলে চুপচাপ উপেক্ষা — ব্রাউজারে সেভ তো আছেই) */
    public function complete(Request $request, string $lesson): JsonResponse
    {
        $lessonData = Lessons::find($lesson);
        abort_if($lessonData === null, 404);
        $acc = max(0, min(100, (int) $request->input('accuracy', 0)));
        \App\Models\ActivityLog::record('lesson_done', "অনুশীলন সম্পন্ন: {$lessonData['title']} (নির্ভুলতা {$acc}%)", ['lesson' => $lesson], $request);

        if (! Schema::hasTable('lesson_progress')) {
            return response()->json(['ok' => true, 'saved' => false]);
        }

        $uid = $request->user()->id;
        $row = DB::table('lesson_progress')->where('user_id', $uid)->where('lesson_id', $lesson)->first();
        if ($row) {
            DB::table('lesson_progress')->where('id', $row->id)->update([
                'best_accuracy' => max((int) $row->best_accuracy, $acc),
                'times' => (int) $row->times + 1,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('lesson_progress')->insert([
                'user_id' => $uid, 'lesson_id' => $lesson, 'best_accuracy' => $acc, 'times' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return response()->json(['ok' => true, 'saved' => true]);
    }

    /** @return array<int, string> */
    private function doneIds(Request $request): array
    {
        if (! Schema::hasTable('lesson_progress')) {
            return [];
        }

        return DB::table('lesson_progress')->where('user_id', $request->user()->id)->pluck('lesson_id')->all();
    }
}
