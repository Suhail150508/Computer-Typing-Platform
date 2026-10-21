<?php

namespace Tests\Unit;

use App\Services\TypingEvaluator;
use PHPUnit\Framework\TestCase;

class TypingEvaluatorTest extends TestCase
{
    private TypingEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new TypingEvaluator();
    }

    public function test_reference_case_matches_wpm_formula(): void
    {
        // ১৫ সেকেন্ডে ২৪ ক্যারেক্টার => (24/5) / 0.25 = 19.2 WPM
        $original = 'ডিজিটাল সাক্ষরতা হলো আধুনিক তথ্যপ্রযুক্তি ভিত্তিক সমাজে';
        $r = $this->evaluator->evaluate($original, 'ডিজিটাল সাক্ষরতা হলো আধুনিক', 15, 300);

        $this->assertSame(24, $r['correct_chars_no_space']);
        $this->assertEqualsWithDelta(19.2, $r['wpm'], 0.001);
        $this->assertEqualsWithDelta(100.0, $r['accuracy'], 0.001);
        $this->assertSame(4, $r['diff_meta']['typed_words_by_stroke']);
        $this->assertSame('fail', $r['result'], 'সময়ের ১৫% মাত্র টাইপ করা হয়েছে, তাই ফেল');
    }

    public function test_skipped_word_counts_as_single_error_when_not_strict(): void
    {
        $r = $this->evaluator->evaluate('a b c d e f g h', 'a b d e f g h', 60, 60, 1, 50, 50, false);

        $this->assertSame(1, $r['wrong_words']);
        $this->assertSame(1, $r['diff_meta']['counts']['missed']);
        $this->assertSame(7, $r['diff_meta']['counts']['equal']);
    }

    public function test_skipped_word_makes_everything_after_wrong_in_strict_mode(): void
    {
        $r = $this->evaluator->evaluate('a1111 b2222 c3333 d4444 e5555 f6666 g7777 h8888', 'a1111 b2222 d4444 e5555 f6666 g7777 h8888', 60, 60, 1, 50, 50);

        $this->assertSame(2, $r['diff_meta']['counts']['equal']);
        $this->assertSame(5, $r['diff_meta']['counts']['after_skip']);
        $this->assertSame(6, $r['wrong_words']);
        $this->assertSame('missed', $r['diff_meta']['skip_break']['type']);
        $this->assertSame(3, $r['diff_meta']['skip_break']['position']);
        $this->assertSame(2, $r['diff_meta']['typed_words_by_stroke']); // শুধু বাদ পড়ার আগের ১০ ক্যারেক্টার সঠিক
        $this->assertSame('fail', $r['result']);
    }

    public function test_plain_typo_does_not_trigger_strict_break(): void
    {
        $r = $this->evaluator->evaluate('a1111 b2222 c3333 d4444', 'a1111 bXXXX c3333 d4444', 60, 60, 1, 50, 50);

        $this->assertNull($r['diff_meta']['skip_break']);
        $this->assertSame(1, $r['diff_meta']['counts']['wrong']);
        $this->assertSame(3, $r['diff_meta']['counts']['equal']);
    }

    public function test_misspelled_word_is_flagged(): void
    {
        $r = $this->evaluator->evaluate('the quick brown fox jumps', 'the quikc brown fox jumps', 60, 60, 1, 50, 50);

        $this->assertSame(1, $r['diff_meta']['counts']['wrong']);
        $this->assertEqualsWithDelta(80.0, $r['accuracy'], 0.001);
    }

    public function test_extra_word_is_flagged(): void
    {
        $r = $this->evaluator->evaluate('one two three four', 'one two extra three four', 60, 60, 1, 50, 50);

        $this->assertSame(1, $r['diff_meta']['counts']['extra']);
        $this->assertSame(0, $r['diff_meta']['counts']['wrong']);
    }

    public function test_untyped_tail_is_not_an_error(): void
    {
        $r = $this->evaluator->evaluate('a b c d e f g h i j', 'a b c', 60, 60, 1, 50, 50);

        $this->assertSame(0, $r['wrong_words']);
        $this->assertSame(3, $r['diff_meta']['consumed']);
    }

    public function test_pass_and_fail_rules(): void
    {
        $slow = $this->evaluator->evaluate(str_repeat('abcde ', 50), str_repeat('abcde ', 50), 300, 300, 20, 90, 90);
        $this->assertSame('fail', $slow['result']);
        $this->assertEqualsWithDelta(10.0, $slow['wpm'], 0.001);

        $fast = $this->evaluator->evaluate(str_repeat('abcde ', 500), str_repeat('abcde ', 500), 300, 300, 20, 90, 90);
        $this->assertSame('pass', $fast['result']);
        $this->assertEqualsWithDelta(100.0, $fast['wpm'], 0.001);
    }

    public function test_empty_input_fails_safely(): void
    {
        $r = $this->evaluator->evaluate('a b c', "   \n ", 10, 60);

        $this->assertSame('fail', $r['result']);
        $this->assertSame(0.0, (float) $r['wpm']);
    }

    public function test_decomposed_and_precomposed_bangla_nukta_letters_match(): void
    {
        // "ড়" দুইভাবে এনকোড হতে পারে: একটাই কোড পয়েন্ট (precomposed), অথবা
        // "ড" + আলাদা নুক্তা চিহ্ন (decomposed) — দেখতে অভিন্ন কিন্তু বাইট আলাদা।
        // normalize() ছাড়া এই তুলনা ভুল করে "wrong" ধরে ফেলত।
        $original = 'বই পড়লে জ্ঞান বাড়ে'; // precomposed ড়
        $typedDecomposed = "বই প\u{09A1}\u{09BC}লে জ্ঞান বা\u{09A1}\u{09BC}ে"; // decomposed ড + ়

        $r = $this->evaluator->evaluate($original, $typedDecomposed, 10, 60, 1, 50, 50);

        $this->assertSame(0, $r['wrong_words']);
        $this->assertEqualsWithDelta(100.0, $r['accuracy'], 0.001);
    }
}
