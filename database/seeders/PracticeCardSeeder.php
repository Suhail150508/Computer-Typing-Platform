<?php

namespace Database\Seeders;

use App\Models\PracticeCard;
use Illuminate\Database\Seeder;

class PracticeCardSeeder extends Seeder
{
    /** বেসিক (বাংলা): ছোট, সহজ শব্দ, সাধারণ যতিচিহ্ন — একেকটি সম্পূর্ণ আলাদা অনুচ্ছেদ */
    private array $basicBn = [
        'প্রতিদিন সকালে ঘুম থেকে উঠে হাত-মুখ ধুয়ে নেওয়া একটি ভালো অভ্যাস। এতে শরীর ও মন দুটোই সতেজ থাকে এবং সারাদিনের কাজে মনোযোগ বসে।',
        'বই মানুষের সবচেয়ে ভালো বন্ধু। প্রতিদিন অল্প কিছু সময় বই পড়লে জ্ঞান বাড়ে এবং নতুন নতুন বিষয় জানা যায়। তাই সবার উচিত নিয়মিত বই পড়া।',
        'পরিষ্কার পরিবেশ সুস্থ জীবনের প্রথম শর্ত। আমাদের চারপাশ পরিষ্কার রাখলে রোগ-জীবাণু কম ছড়ায় এবং সবাই সুস্থ থাকে। তাই ময়লা যেখানে সেখানে না ফেলে নির্দিষ্ট স্থানে ফেলা উচিত।',
        'সময়ের কাজ সময়ে করা একটি বড় গুণ। যারা সময়মতো কাজ শেষ করে, তারা জীবনে সহজেই সফল হয়। অলসতা করে কাজ ফেলে রাখলে পরে চাপ বেড়ে যায়।',
        'পরিবারের সবার সাথে প্রতিদিন কিছুটা সময় কাটানো উচিত। এতে পারিবারিক বন্ধন মজবুত হয় এবং মানসিক শান্তি পাওয়া যায়। ব্যস্ত জীবনেও এই অভ্যাস ধরে রাখা জরুরি।',
        'প্রতিদিন হাঁটাহাঁটি বা হালকা ব্যায়াম করলে শরীর ভালো থাকে। শরীরচর্চা রক্ত চলাচল বাড়ায় এবং মনকে চাঙা রাখে। তাই কর্মব্যস্ত দিনেও কিছুটা সময় বের করা উচিত।',
        'গাছ আমাদের অক্সিজেন দেয় এবং পরিবেশকে সুন্দর করে তোলে। বেশি করে গাছ লাগালে বাতাস পরিষ্কার থাকে এবং তাপমাত্রা নিয়ন্ত্রণে থাকে। তাই প্রতিটি মানুষের উচিত অন্তত একটি গাছ লাগানো।',
        'নতুন কিছু শেখার আগ্রহ মানুষকে এগিয়ে নিয়ে যায়। প্রতিদিন একটু একটু করে নতুন দক্ষতা অর্জন করলে ভবিষ্যতে অনেক কাজে লাগে। তাই শেখার আগ্রহ কখনো কমতে দেওয়া উচিত নয়।',
    ];

    /** অ্যাডভান্স (বাংলা): মাঝারি জটিল বাক্য, স্বাভাবিক যতিচিহ্ন */
    private array $advanceParasBn = [
        "আধুনিক বিশ্বে তথ্যপ্রযুক্তি আমাদের জীবনযাত্রাকে সম্পূর্ণভাবে বদলে দিয়েছে। মোবাইল ফোন ও ইন্টারনেটের মাধ্যমে আমরা এখন মুহূর্তেই বিশ্বের যেকোনো প্রান্তের খবর জানতে পারি, দূরে থাকা প্রিয়জনের সাথে ভিডিও কলে কথা বলতে পারি এবং ঘরে বসেই কেনাকাটা, ব্যাংকিং বা পড়াশোনার কাজ সেরে ফেলতে পারি।",
        "তবে এই সুবিধার পাশাপাশি সচেতনতাও জরুরি, কারণ ইন্টারনেটে ভুয়া তথ্য ও প্রতারণার ফাঁদও কম নয়। তাই যেকোনো তথ্য বিশ্বাস করার আগে তার উৎস যাচাই করা এবং ব্যক্তিগত তথ্য সবার সাথে শেয়ার না করার অভ্যাস গড়ে তোলা প্রয়োজন।",
        "শিক্ষা জাতির মেরুদণ্ড হিসেবে পরিচিত, কারণ একটি শিক্ষিত জাতিই কেবল প্রকৃত উন্নয়নের পথে এগিয়ে যেতে পারে। শুধু পরীক্ষায় ভালো ফলাফল করাই শিক্ষার একমাত্র লক্ষ্য নয়; বরং যুক্তিবোধ, নৈতিকতা এবং সৃজনশীলতা তৈরি করাও শিক্ষার গুরুত্বপূর্ণ অংশ।",
        "বর্তমানে অনলাইন প্ল্যাটফর্মের কল্যাণে শিক্ষার্থীরা ঘরে বসেই দেশ-বিদেশের সেরা শিক্ষকদের ক্লাস করার সুযোগ পাচ্ছে। ফলে আর্থিকভাবে অস্বচ্ছল শিক্ষার্থীরাও এখন মানসম্মত শিক্ষা গ্রহণের সুযোগ পাচ্ছে, যা কয়েক বছর আগেও কল্পনা করা কঠিন ছিল।",
        "জলবায়ু পরিবর্তনের প্রভাব এখন আমাদের চোখের সামনেই স্পষ্ট। অতিরিক্ত তাপমাত্রা, অনিয়মিত বৃষ্টিপাত এবং ঘন ঘন প্রাকৃতিক দুর্যোগ প্রমাণ করে যে পৃথিবী দ্রুত বদলে যাচ্ছে। বিজ্ঞানীরা বহু বছর ধরে সতর্ক করে আসছেন যে, এখনই ব্যবস্থা না নিলে পরিস্থিতি আরও খারাপ হবে।",
        "এই সংকট মোকাবিলায় ব্যক্তি পর্যায়ে ছোট ছোট পদক্ষেপও গুরুত্বপূর্ণ ভূমিকা রাখতে পারে। যেমন, অপ্রয়োজনীয় বিদ্যুৎ ব্যবহার কমানো, পলিথিনের বদলে কাপড়ের ব্যাগ ব্যবহার করা এবং বেশি করে গাছ লাগানো—এসব ছোট অভ্যাসই একসময় বড় পরিবর্তন আনতে পারে।",
        "সময় ব্যবস্থাপনা একটি গুরুত্বপূর্ণ দক্ষতা, যা শেখা না থাকলে অনেক প্রতিভাবান মানুষও পিছিয়ে পড়েন। প্রতিদিনের কাজগুলোকে গুরুত্ব অনুযায়ী ভাগ করে নিলে এবং একটি নির্দিষ্ট রুটিন মেনে চললে অল্প সময়েই অনেক বেশি কাজ শেষ করা সম্ভব হয়।",
        "পক্ষান্তরে, পরিকল্পনাহীনভাবে কাজ করলে সময় ও পরিশ্রম দুটোই নষ্ট হয়। তাই প্রতিদিন সকালে কয়েক মিনিট সময় নিয়ে দিনের কাজের তালিকা তৈরি করা এবং সবচেয়ে গুরুত্বপূর্ণ কাজটি আগে শেষ করার অভ্যাস গড়ে তোলা উচিত।",
    ];

    /** হার্ড (বাংলা): জটিল বাক্য, সংখ্যা, শতাংশ, উদ্ধৃতি */
    private array $hardParasBn = [
        "বাংলাদেশ পরিসংখ্যান ব্যুরোর সাম্প্রতিক এক প্রতিবেদন অনুযায়ী, দেশের ইন্টারনেট ব্যবহারকারীর সংখ্যা গত ১০ বছরে প্রায় ৪৫০% বৃদ্ধি পেয়েছে; বিশেষজ্ঞদের মতে, ২০৩০ সালের মধ্যে এই সংখ্যা মোট জনসংখ্যার ৮৫% ছাড়িয়ে যেতে পারে।",
        "একজন প্রযুক্তিবিদ এ প্রসঙ্গে মন্তব্য করেছেন, \"শুধু সংযোগ বাড়ালেই হবে না; ব্যবহারকারীদের ডিজিটাল সাক্ষরতা ও তথ্য যাচাইয়ের দক্ষতাও একই হারে বাড়াতে হবে।\" তাঁর এই বক্তব্য বর্তমান বাস্তবতায় বিশেষভাবে প্রাসঙ্গিক, কেননা প্রতিদিন গড়ে ৩২ লাখেরও বেশি নতুন কনটেন্ট বিভিন্ন প্ল্যাটফর্মে যুক্ত হচ্ছে।",
        "এই বিপুল তথ্যপ্রবাহের মধ্যে সঠিক তথ্য বেছে নেওয়া ক্রমেই কঠিন হয়ে উঠছে; ফলে গুজব প্রতিরোধ, সাইবার নিরাপত্তা এবং তথ্য-যাচাইয়ের ওপর গুরুত্ব দেওয়া এখন আগের যেকোনো সময়ের চেয়ে বেশি জরুরি হয়ে দাঁড়িয়েছে।",
        "বিশ্ব স্বাস্থ্য সংস্থার ২০২৪ সালের একটি সমীক্ষায় দেখা গেছে, বিশ্বব্যাপী প্রাপ্তবয়স্কদের প্রায় ৩১% পর্যাপ্ত শারীরিক পরিশ্রম করেন না; এর ফলে হৃদরোগ, ডায়াবেটিস ও উচ্চ রক্তচাপের মতো জটিলতা ক্রমান্বয়ে বেড়ে চলেছে।",
        "একজন চিকিৎসক এ বিষয়ে বলেন, \"সপ্তাহে অন্তত ১৫০ মিনিট মাঝারি মাত্রার শরীরচর্চা—যেমন দ্রুত হাঁটা বা সাইক্লিং—৩০-৪০% পর্যন্ত ঝুঁকি কমাতে পারে।\" অথচ বাস্তবে অধিকাংশ মানুষ কর্মব্যস্ততার অজুহাতে এই সহজ পরামর্শটুকু মেনে চলেন না।",
        "বিশেষজ্ঞরা তাই পরামর্শ দিচ্ছেন, প্রতিদিনের রুটিনে অন্তত ২০-৩০ মিনিট শারীরিক কার্যক্রম যুক্ত করা উচিত; কারণ ধারাবাহিকতাই দীর্ঘমেয়াদে সুস্থতার মূল চাবিকাঠি, একদিনের কঠোর পরিশ্রম নয়।",
        "অর্থনৈতিক বিশ্লেষকদের হিসাব অনুযায়ী, নিয়মিত সঞ্চয়ের অভ্যাস থাকা একটি পরিবারের আয়ের মাত্র ১০-১৫% জমিয়ে রাখলেও ১৫ বছরে তা মূল আয়ের প্রায় ২ থেকে ৩ গুণে পরিণত হতে পারে, যদি তা সঠিকভাবে বিনিয়োগ করা হয়।",
        "একজন অর্থনীতিবিদের ভাষায়, \"সঞ্চয় মানে শুধু টাকা জমানো নয়; এটি ভবিষ্যতের অনিশ্চয়তার বিরুদ্ধে একধরনের প্রস্তুতি।\" তাই আয়ের সিংহভাগ ব্যয় করার আগে একটি নির্দিষ্ট অংশ—চাই তা ৫% হোক বা ২০%—আলাদা করে রাখার অভ্যাস গড়ে তোলা জরুরি; কারণ পরিকল্পনাহীন ব্যয় দীর্ঘমেয়াদে আর্থিক নিরাপত্তাকে ঝুঁকিতে ফেলে।",
    ];

    /** Basic (English): short, simple sentences */
    private array $basicEn = [
        'Waking up early and starting the day with a glass of water is a simple habit that keeps the body fresh and the mind ready for the day ahead.',
        'Reading a good book for a few minutes every day quietly builds new knowledge and opens the mind to fresh and interesting ideas.',
        'A clean environment is the first step toward a healthy life. Keeping our surroundings tidy helps prevent disease and keeps everyone feeling well.',
        'Doing today\'s work today is a great habit. People who finish tasks on time usually find success much easier to reach in life.',
        'Spending a little quality time with family every day makes the bond stronger and brings a calm, peaceful mind after a busy day.',
        'A short walk or light exercise every morning keeps the body active and the mind fresh for the rest of the busy day.',
        'Trees give us oxygen and make our surroundings beautiful. Planting more trees keeps the air clean and the climate a little cooler.',
        'A strong desire to learn new things always moves a person forward. Small daily effort in learning pays off greatly in the future.',
    ];

    /** Advance (English): medium-length paragraphs with normal punctuation */
    private array $advanceParasEn = [
        "Modern technology has completely changed the way people live and work today. With a mobile phone and an internet connection, we can read the news from any corner of the world, talk face to face with loved ones far away, and even finish our shopping, banking, or studies without leaving home.",
        "Along with these conveniences, however, awareness is just as important, because the internet is also full of false information and clever scams. Before trusting anything online, it is wise to check the source carefully and to avoid sharing personal details with strangers on any platform.",
        "Education is often called the backbone of a nation, because only an educated society can truly move toward lasting progress. Good grades alone are not the only goal of learning; building reasoning skills, honesty, and creativity matters just as much for a well-rounded life.",
        "Thanks to online platforms, students today can join classes taught by some of the best teachers in the world without ever leaving their homes. As a result, even students from less privileged backgrounds now have a real chance at quality education that once seemed impossible.",
        "The effects of climate change are now visible all around us. Rising temperatures, unpredictable rainfall, and frequent natural disasters all show that the planet is changing faster than expected, and scientists have been warning about this for many years already.",
        "Small everyday actions can still make a real difference in facing this crisis. Turning off unused lights, choosing cloth bags over plastic, and planting more trees are simple habits that, over time, add up to meaningful change for the environment.",
        "Time management is a valuable skill, and without it even highly talented people can fall behind. Dividing daily tasks by importance and following a simple routine allows a person to finish far more work in far less time.",
        "On the other hand, working without any plan often wastes both time and energy. Spending just a few minutes each morning to list the day's tasks, and tackling the most important one first, is a habit worth building early.",
    ];

    /** Hard (English): longer, with numbers, quotes, percentages */
    private array $hardParasEn = [
        "According to a recent national survey, the number of internet users in the country has grown by nearly 450% over the past ten years; experts now predict that this figure could cross 85% of the total population by the year 2030.",
        "A technology expert commented on this trend, saying, \"Expanding connectivity alone is not enough; digital literacy and fact-checking skills must grow at the same pace.\" This remark feels especially relevant today, since more than 3.2 million new pieces of content are published online every single day.",
        "Amid this flood of information, choosing what to trust has become increasingly difficult; as a result, fighting misinformation, protecting cyber security, and verifying facts have never been more important than they are right now.",
        "A 2024 global health survey found that nearly 31% of adults worldwide do not get enough physical activity; this shortfall has been linked to a steady rise in heart disease, diabetes, and high blood pressure across many countries.",
        "A physician explained, \"At least 150 minutes of moderate exercise a week — such as brisk walking or cycling — can lower that risk by 30% to 40%.\" Yet in practice, most people skip this simple advice, citing a busy schedule as the reason.",
        "Experts therefore recommend adding at least 20 to 30 minutes of physical activity to the daily routine; consistency, not one intense workout, is ultimately the real key to long-term health and wellbeing.",
        "Financial analysts estimate that a family which regularly saves just 10% to 15% of its income could see that amount grow to two or three times its original value within 15 years, provided it is invested wisely.",
        "An economist once put it this way: \"Saving isn't only about setting money aside; it's a form of preparation against an uncertain future.\" Setting aside a fixed share of income — whether it's 5% or 20% — before spending the rest is a habit worth building early, since unplanned spending can quietly threaten long-term financial security.",
    ];

    public function run(): void
    {
        $this->seedBasic('bangla', $this->basicBn, [60, 60, 60, 60, 180, 180, 180, 180]);
        $this->seedBasic('english', $this->basicEn, [60, 60, 60, 60, 180, 180, 180, 180]);

        $this->seedCombined('advance', 'bangla', $this->advanceParasBn, [180, 180, 180, 180, 300, 300, 300, 300], 20, 2);
        $this->seedCombined('advance', 'english', $this->advanceParasEn, [180, 180, 180, 180, 300, 300, 300, 300], 18, 2);

        $this->seedCombined('hard', 'bangla', $this->hardParasBn, [180, 180, 300, 300, 600, 600, 900, 900], 30, 2);
        $this->seedCombined('hard', 'english', $this->hardParasEn, [180, 180, 300, 300, 600, 600, 900, 900], 26, 2);
    }

    /**
     * বেসিক কার্ড তৈরি করে — প্রতিটির নিজস্ব সময় (durations প্যারামিটার থেকে)।
     * ১ মিনিটের কার্ডে একটাই অনুচ্ছেদ যথেষ্ট, কিন্তু ৩ মিনিটের কার্ডে লক্ষ্য WPM অনুযায়ী
     * প্রয়োজনীয় চরিত্র-সংখ্যা হিসাব করে পুল থেকে একাধিক অনুচ্ছেদ জোড়া লাগানো হয়,
     * যাতে দ্রুত টাইপিস্টও পুরো সময় টাইপ করার মতো যথেষ্ট কনটেন্ট পান।
     *
     * @param  list<string>  $pool
     * @param  list<int>  $durations  প্রতিটি কার্ডের সময় (সেকেন্ডে), pool এর সমান দৈর্ঘ্যের
     */
    private function seedBasic(string $language, array $pool, array $durations): void
    {
        $wpmSteps = $language === 'bangla' ? [8, 10, 12, 14, 15, 16, 18, 20] : [10, 12, 14, 16, 18, 20, 22, 24];
        $accSteps = [78, 80, 82, 84, 86, 87, 88, 90];
        $durPctSteps = [65, 70, 70, 75, 75, 80, 85, 90];
        $titlesBn = ['ঘুম থেকে ওঠা', 'বই পড়া', 'পরিচ্ছন্নতা', 'সময়ানুবর্তিতা', 'পারিবারিক বন্ধন', 'শরীরচর্চা', 'বৃক্ষরোপণ', 'শেখার আগ্রহ'];
        $titlesEn = ['Waking Up', 'Reading Books', 'Cleanliness', 'Punctuality', 'Family Time', 'Exercise', 'Planting Trees', 'Love of Learning'];
        $titles = $language === 'bangla' ? $titlesBn : $titlesEn;
        $label = $language === 'bangla' ? 'বেসিক' : 'Basic';

        $n = count($pool);
        $avgLen = (int) round(array_sum(array_map(fn ($p) => mb_strlen(preg_replace('/\s+/u', '', $p) ?? ''), $pool)) / $n);
        $bufferFactor = 1.6;

        foreach ($pool as $i => $text) {
            $duration = (int) $durations[$i];
            $needChars = $wpmSteps[$i] * 5 * ($duration / 60);
            $take = max(1, (int) ceil($needChars * $bufferFactor / max(1, $avgLen)));

            $selected = [];
            for ($k = 0; $k < $take; $k++) {
                $selected[] = $pool[($i + $k) % $n];
            }

            $this->upsert('basic', $language, $i + 1, [
                'title' => $label.' #'.($i + 1).' — '.$titles[$i],
                'description' => $language === 'bangla'
                    ? "\"{$titles[$i]}\" নিয়ে ছোট, সহজ বাক্যে হাত মকশো করুন।"
                    : "Warm up with a short, simple passage about \"{$titles[$i]}\".",
                'icon' => '🌱',
                'content' => implode("\n\n", $selected),
                'duration_seconds' => $duration,
                'min_wpm' => $wpmSteps[$i],
                'min_accuracy' => $accSteps[$i],
                'min_duration_percent' => $durPctSteps[$i],
            ]);
        }
    }

    /**
     * অ্যাডভান্স/হার্ড কার্ড — প্যারাগ্রাফ পুল থেকে ঘুরিয়ে-ফিরিয়ে কম্বিনেশন তৈরি করে ৮টি ভিন্ন কনটেন্ট বানায়।
     * প্রতিটি কার্ডের নিজস্ব সময় (durations থেকে) এবং প্রয়োজনীয় চরিত্র-সংখ্যা হিসাব করে যথেষ্ট
     * প্যারাগ্রাফ নেওয়া হয়, যাতে লক্ষ্য WPM-এর চেয়ে দ্রুত টাইপিস্টও পুরো সময় টাইপ করার মতো কনটেন্ট পান।
     *
     * @param  list<string>  $pool
     * @param  list<int>  $durations  ৮টি কার্ডের জন্য ৮টি সময় (সেকেন্ডে)
     */
    private function seedCombined(string $tier, string $language, array $pool, array $durations, int $wpmStart, int $wpmStep): void
    {
        $isHard = $tier === 'hard';
        $icon = $isHard ? '🔥' : '🚀';
        $label = match (true) {
            $tier === 'advance' && $language === 'bangla' => 'অ্যাডভান্স',
            $tier === 'advance' => 'Advance',
            $language === 'bangla' => 'হার্ড',
            default => 'Hard',
        };
        $titlesBn = $isHard
            ? ['ইন্টারনেট পরিসংখ্যান', 'ডিজিটাল সাক্ষরতা', 'তথ্য যাচাই', 'স্বাস্থ্য জরিপ', 'চিকিৎসকের পরামর্শ', 'শরীরচর্চার রুটিন', 'সঞ্চয়ের হিসাব', 'অর্থনীতিবিদের মত']
            : ['প্রযুক্তি ও সচেতনতা', 'ডিজিটাল সাক্ষরতা', 'শিক্ষার গুরুত্ব', 'অনলাইন শিক্ষা', 'জলবায়ু পরিবর্তন', 'পরিবেশ রক্ষা', 'সময় ব্যবস্থাপনা', 'পরিকল্পিত জীবন'];
        $titlesEn = $isHard
            ? ['Internet Statistics', 'Digital Literacy', 'Fact-checking', 'Health Survey', "Doctor's Advice", 'Exercise Routine', 'Savings Math', "Economist's View"]
            : ['Technology & Awareness', 'Digital Literacy', 'Importance of Education', 'Online Learning', 'Climate Change', 'Protecting Environment', 'Time Management', 'A Planned Life'];
        $titles = $language === 'bangla' ? $titlesBn : $titlesEn;

        $descBn = $isHard ? 'সংখ্যা, শতাংশ ও উদ্ধৃতিসহ জটিল অনুচ্ছেদ।' : 'স্বাভাবিক যতিচিহ্নসহ মাঝারি দৈর্ঘ্যের অনুচ্ছেদ।';
        $descEn = $isHard ? 'A complex passage with numbers, percentages and quotes.' : 'A medium-length passage with everyday punctuation.';

        $accStart = $isHard ? 90 : 86;
        $durPctStart = $isHard ? 85 : 82;

        $n = count($pool);
        $avgLen = (int) round(array_sum(array_map(fn ($p) => mb_strlen(preg_replace('/\s+/u', '', $p) ?? ''), $pool)) / $n);
        $bufferFactor = 1.6; // লক্ষ্য WPM এর চেয়ে দ্রুত টাইপিস্টের জন্যও যথেষ্ট কনটেন্ট রাখার মার্জিন

        for ($i = 0; $i < 8; $i++) {
            $duration = (int) $durations[$i];
            $wpm = $wpmStart + $i * $wpmStep;
            $needChars = $wpm * 5 * ($duration / 60);
            $take = max(2, (int) ceil($needChars * $bufferFactor / max(1, $avgLen)));

            $selected = [];
            for ($k = 0; $k < $take; $k++) {
                $selected[] = $pool[($i + $k) % $n];
            }

            $topic = $titles[$i % count($titles)];
            $this->upsert($tier, $language, $i + 1, [
                'title' => $label.' #'.($i + 1).' — '.$topic,
                'description' => $language === 'bangla'
                    ? "\"{$topic}\" বিষয়ে {$descBn}"
                    : "A passage about \"{$topic}\" — ".lcfirst($descEn),
                'icon' => $icon,
                'content' => implode("\n\n", $selected),
                'duration_seconds' => $duration,
                'min_wpm' => $wpm,
                'min_accuracy' => min(96, $accStart + intdiv($i, 2)),
                'min_duration_percent' => min(95, $durPctStart + intdiv($i, 2)),
            ]);
        }
    }

    private function upsert(string $tier, string $language, int $order, array $attrs): void
    {
        PracticeCard::updateOrCreate(
            ['tier' => $tier, 'language' => $language, 'order' => $order],
            $attrs + ['tier' => $tier, 'language' => $language, 'order' => $order, 'is_active' => true]
        );
    }
}
