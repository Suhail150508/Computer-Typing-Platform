<?php

namespace Database\Seeders;

use App\Models\Lab;
use App\Models\Passage;
use Illuminate\Database\Seeder;

class PassageSeeder extends Seeder
{
    /** @var list<string> */
    private array $bangla = [
        'বাংলাদেশ নদীমাতৃক দেশ। এ দেশের বুক চিরে বয়ে গেছে অসংখ্য নদী, যা যুগ যুগ ধরে আমাদের কৃষি, যোগাযোগ ও সংস্কৃতিকে সমৃদ্ধ করে আসছে। নদীর পলিমাটিতে ফলে সোনালি ফসল, আর নদীর ঢেউয়ে ভেসে চলে মাঝির গান। কিন্তু অপরিকল্পিত শিল্পায়ন ও দখলের কারণে আজ অনেক নদী তার প্রাণ হারাচ্ছে। তাই নদী রক্ষায় সবাইকে সচেতন হতে হবে এবং প্রকৃতির সঙ্গে সামঞ্জস্য রেখে উন্নয়নের পথে এগোতে হবে।',
        'শিক্ষা মানুষের সবচেয়ে বড় সম্পদ। একটি জাতি যত বেশি শিক্ষিত হয়, সে জাতি তত দ্রুত উন্নতির শিখরে পৌঁছাতে পারে। আধুনিক যুগে শুধু পুঁথিগত বিদ্যা যথেষ্ট নয়; প্রয়োজন হাতে-কলমে শেখা এবং প্রযুক্তির সঠিক ব্যবহার জানা। কম্পিউটার ও ইন্টারনেটের সহজলভ্যতা শিক্ষার্থীদের সামনে জ্ঞানের অসীম দুয়ার খুলে দিয়েছে। এখন প্রয়োজন সেই সুযোগকে কাজে লাগিয়ে নিজেকে দক্ষ মানবসম্পদে রূপান্তর করা।',
        'সময়ের মূল্য বুঝতে পারা সফল মানুষের একটি বড় গুণ। যে ব্যক্তি সময়ের সঠিক ব্যবহার করে, জীবনের প্রতিটি ক্ষেত্রে সে সাফল্য অর্জন করে। প্রতিদিন নিয়ম করে অল্প অল্প অনুশীলন করলে যে কোনো দক্ষতাই ধীরে ধীরে আয়ত্ত করা সম্ভব। টাইপিং শেখার ক্ষেত্রেও একই কথা প্রযোজ্য। নিয়মিত চর্চা, ধৈর্য ও মনোযোগ থাকলে গতি ও শুদ্ধতা দুটোই বাড়ানো যায়, আর তখন কাজ করা হয়ে ওঠে আনন্দের।',
        'আমাদের মাতৃভাষা বাংলা পৃথিবীর অন্যতম সমৃদ্ধ ভাষা। ভাষা আন্দোলনের আত্মত্যাগের বিনিময়ে আমরা এই ভাষায় কথা বলার অধিকার অর্জন করেছি। ডিজিটাল যুগে এই ভাষাকে কম্পিউটার ও মোবাইলে সহজে ব্যবহারের উপযোগী করে তোলা আমাদের সবারই দায়িত্ব। শুদ্ধ বানানে দ্রুত বাংলা টাইপ করতে পারা তাই শুধু একটি দক্ষতা নয়, বরং ভাষার প্রতি ভালোবাসা প্রকাশের একটি সুন্দর উপায়।',
    ];

    /** @var list<string> */
    private array $english = [
        'Typing is one of the most useful skills in the modern workplace. Whether you are writing an email, preparing a report, or entering data into a spreadsheet, the ability to type quickly and accurately saves a great deal of time every single day. Like any other skill, it improves with steady practice, and even fifteen minutes of focused effort each day can produce visible progress within a few weeks.',
        'A good typist does not look at the keyboard while working. Instead, the fingers rest on the home row and learn to reach every key by feel. At first this feels slow and uncomfortable, but muscle memory develops faster than most beginners expect. Keeping the wrists relaxed, the back straight, and the eyes on the screen helps prevent fatigue and keeps mistakes to a minimum during long sessions.',
        'Accuracy matters more than raw speed, especially in official examinations. A page full of errors takes longer to correct than a page typed slightly slower but cleanly. Therefore, beginners should first aim for precision and let speed follow naturally. Reading the next few words ahead while typing the current one is a powerful habit that smooths out rhythm and reduces hesitation between words.',
        'Technology continues to change the way people work and learn across the world. Digital skills, including typing, are now a basic requirement for many jobs in both the public and private sectors. Those who invest time in mastering them early gain a clear advantage, because confidence with the keyboard makes every other computer task easier, faster, and far more enjoyable to complete.',
    ];

    public function run(): void
    {
        foreach (Lab::all() as $lab) {
            foreach (['bangla' => $this->bangla, 'english' => $this->english] as $language => $paragraphs) {
                // প্রতিটি ল্যাবে ৪টি সেট: অনুচ্ছেদের ক্রম ঘুরিয়ে আলাদা প্যাসেজ বানানো হয়
                for ($set = 0; $set < 4; $set++) {
                    $rotated = array_merge(array_slice($paragraphs, $set), array_slice($paragraphs, 0, $set));
                    $title = $lab->name_en.' - '.ucfirst($language).' - Set '.($set + 1);

                    Passage::updateOrCreate(
                        ['lab_id' => $lab->id, 'language' => $language, 'title' => $title],
                        ['content' => implode("\n\n", $rotated), 'is_active' => true]
                    );
                }
            }
        }
    }
}
