<?php

namespace App\Services;

use App\Models\PracticeCard;
use App\Models\Setting;
use App\Models\TypingAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * "টাইপিং শুরু" এর টায়ার-আনলক ও কার্ড-স্ট্যাটাস হিসাব করে।
 *
 * নিয়ম:
 *  - Basic টায়ার সবসময় খোলা।
 *  - একটি টায়ারের অন্তত N (কনফিগ) টা ভিন্ন কার্ড পাস করলে তার পরের টায়ার আনলক হয়।
 *  - একটি টায়ারের ভেতরের সব কার্ড (টায়ার আনলক থাকলে) একসাথে খোলা থাকে —
 *    কঠিনতার ক্রম অনুযায়ী সাজানো থাকে, কিন্তু জোর করে ধারাবাহিকভাবে খেলানো হয় না।
 *  - বাংলা ও English সম্পূর্ণ আলাদা অগ্রগতি হিসেবে ট্র্যাক হয় (একটি ভাষায় দক্ষতা
 *    অন্য ভাষায় দক্ষতার প্রমাণ নয়), তাই প্রতিটি ভাষার নিজস্ব বেসিক→অ্যাডভান্স→হার্ড পথ আছে।
 */
class PracticeProgressService
{
    private readonly int $threshold;

    /** $threshold না দিলে অ্যাডমিন সেটিংস (না থাকলে config/typing.php) থেকে মান নেয় */
    public function __construct(?int $threshold = null)
    {
        $this->threshold = max(1, $threshold ?? Setting::typing('practice_unlock_threshold'));
    }

    /**
     * @return array<string, int> প্রতিটি টায়ারে (নির্দিষ্ট ভাষায়) ইউজারের পাস করা ভিন্ন কার্ডের সংখ্যা
     */
    public function passedCountsByTier(User $user, string $language): array
    {
        $rows = TypingAttempt::query()
            ->join('practice_cards', 'practice_cards.id', '=', 'typing_attempts.practice_card_id')
            ->where('typing_attempts.user_id', $user->id)
            ->where('typing_attempts.source', 'practice')
            ->where('typing_attempts.result', 'pass')
            ->where('practice_cards.language', $language)
            ->selectRaw('practice_cards.tier as tier, count(distinct typing_attempts.practice_card_id) as cnt')
            ->groupBy('practice_cards.tier')
            ->pluck('cnt', 'tier');

        return [
            'basic' => (int) ($rows['basic'] ?? 0),
            'advance' => (int) ($rows['advance'] ?? 0),
            'hard' => (int) ($rows['hard'] ?? 0),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function unlockedTiers(User $user, string $language): array
    {
        $passed = $this->passedCountsByTier($user, $language);

        return [
            'basic' => true,
            'advance' => $passed['basic'] >= $this->threshold,
            'hard' => $passed['advance'] >= $this->threshold,
        ];
    }

    public function isTierUnlocked(User $user, string $tier, string $language): bool
    {
        return $this->unlockedTiers($user, $language)[$tier] ?? false;
    }

    /**
     * প্রতিটি টায়ারের জন্য (নির্দিষ্ট ভাষায়) কার্ড তালিকা + প্রতিটির স্ট্যাটাস ও সেরা ফলাফল সহ।
     *
     * @return array<string, array{unlocked: bool, passed: int, threshold: int, next_needs: int, cards: Collection}>
     */
    public function board(User $user, string $language): array
    {
        $unlocked = $this->unlockedTiers($user, $language);
        $passedCounts = $this->passedCountsByTier($user, $language);

        $bestByCard = TypingAttempt::query()
            ->where('user_id', $user->id)
            ->where('source', 'practice')
            ->whereNotNull('practice_card_id')
            ->orderByDesc('wpm')
            ->get(['practice_card_id', 'wpm', 'accuracy', 'result'])
            ->groupBy('practice_card_id')
            ->map(function (Collection $attempts) {
                return [
                    'passed' => $attempts->contains('result', 'pass'),
                    'best_wpm' => (float) $attempts->max('wpm'),
                    'best_accuracy' => (float) $attempts->max('accuracy'),
                    'tries' => $attempts->count(),
                ];
            });

        $board = [];
        foreach (PracticeCard::TIERS as $tier) {
            $cards = PracticeCard::query()->tier($tier)->language($language)->active()->get()
                ->map(function (PracticeCard $card) use ($bestByCard, $unlocked, $tier) {
                    $best = $bestByCard->get($card->id);

                    return (object) [
                        'card' => $card,
                        'locked' => ! ($unlocked[$tier] ?? false),
                        'passed' => (bool) ($best['passed'] ?? false),
                        'tries' => (int) ($best['tries'] ?? 0),
                        'best_wpm' => (float) ($best['best_wpm'] ?? 0),
                        'best_accuracy' => (float) ($best['best_accuracy'] ?? 0),
                    ];
                });

            $board[$tier] = [
                'unlocked' => $unlocked[$tier],
                'passed' => $passedCounts[$tier],
                'threshold' => $this->threshold,
                'next_needs' => max(0, $this->threshold - $passedCounts[$tier]),
                'cards' => $cards,
            ];
        }

        return $board;
    }

    public function allTiersCompleted(User $user, string $language): bool
    {
        $passed = $this->passedCountsByTier($user, $language);

        return $passed['hard'] >= $this->threshold;
    }
}
