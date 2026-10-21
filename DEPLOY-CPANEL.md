# cPanel-এ ডেপ্লয় গাইড (লোকালেও একই কোড চলবে)

## আগে একটা জরুরি কথা: "This site can't be reached" মানে কী?
এটা **কোডের এরর নয়**। কোডে ভুল থাকলে ব্রাউজারে 500/404/403 পেজ আসত। "can't be reached" মানে ব্রাউজার সার্ভারেই পৌঁছাতে পারেনি। কারণ সাধারণত:
1. **ডোমেইন সার্ভারে পয়েন্ট করা নেই** — ডোমেইনের Nameserver হোস্টিংয়ের NS হতে হবে (cPanel-এর ইমেইলে দেওয়া থাকে), বা A record হোস্টিংয়ের IP-তে। বদলানোর পর ২–২৪ ঘণ্টা লাগতে পারে।
2. **ডোমেইন cPanel-এ যোগ করা নেই** — cPanel → Domains → Create A New Domain / Addon Domain।
3. **`https://` দিয়ে খুলছেন কিন্তু SSL এখনো ইস্যু হয়নি** — cPanel → SSL/TLS Status → Run AutoSSL; আপাতত `http://` দিয়ে খুলে দেখুন।
4. **সাময়িক টেস্ট লিংক** — হোস্টিং কোম্পানির দেওয়া টেম্প URL / `http://সার্ভার-IP/~cpanelUser/` দিয়ে খুলে দেখুন; সেটা খুললে সমস্যা ডোমেইন/DNS-এ।
প্রথমে একটা `info.html` ফাইল public_html-এ দিয়ে ডোমেইন থেকে খুলে দেখুন — সেটা না খুললে সমস্যা ১–৪ নম্বরের কোনোটা।

## ধাপ ১ — লোকালে প্রস্তুতি (vendor ফোল্ডার লাগবে)
```
composer install --no-dev --optimize-autoloader
php artisan key:generate --show      # যা আসবে সেটা APP_KEY-তে বসাবেন
```
শেয়ার্ড হোস্টিংয়ে প্রায়ই composer থাকে না, তাই **vendor ফোল্ডার সহ** আপলোড করুন।
PHP ভার্সন হতে হবে **৮.২+** (cPanel → MultiPHP Manager; দরকারি extension: mbstring, intl, pdo_mysql, xml, curl, fileinfo)।

## ধাপ ২ — আপলোড (দুইটি উপায়, ১ নম্বর সুপারিশকৃত)
**উপায় ১ (নিরাপদ):** 
- পুরো প্রজেক্ট (public ছাড়া) → `/home/cpuser/typingbd/` (public_html-এর **বাইরে**)
- `public/` এর ভেতরের সব ফাইল (index.php, .htaccess, css, js, uploads) → `public_html/`
- `public_html/index.php` খুলে দুই লাইনে `__DIR__.'/../` বদলে `__DIR__.'/../typingbd/` করুন (vendor/autoload.php ও bootstrap/app.php)।

**উপায় ২ (সহজ):** পুরো প্রজেক্ট ফোল্ডারের সব ফাইল সরাসরি `public_html/`-এ আপলোড। আমি রুটে `.htaccess` দিয়েছি যা সবকিছু `public/`-এ পাঠায় এবং `.env`/`app`/`vendor` ব্লক করে। (.htaccess হিডেন ফাইল — File Manager-এ "Show Hidden Files" অন করুন।)

## ধাপ ৩ — .env ও ডাটাবেস
1. cPanel → **MySQL Databases**: ডাটাবেস + ইউজার বানিয়ে ইউজারকে ডাটাবেসে All Privileges দিন।
2. `.env.cpanel.example` কপি করে নাম দিন `.env`; DB তথ্য, APP_URL (`https://yourdomain.com`), APP_KEY বসান। **APP_DEBUG=false** রাখুন।
3. ডাটাবেস টেবিল তৈরি: Terminal থাকলে `php artisan migrate --force && php artisan db:seed --force`। Terminal না থাকলে লোকালে MySQL দিয়ে `migrate` + `db:seed` চালিয়ে phpMyAdmin → Export → সার্ভারে Import করুন।
4. ফোল্ডার পারমিশন: `storage/` ও `bootstrap/cache/` → **775** (রিকার্সিভ), `public/uploads` → 775।

## এরর দেখলে
| লক্ষণ | সমাধান |
|---|---|
| 500 Internal Server Error | `storage/logs/laravel.log` দেখুন; সাধারণত APP_KEY খালি, vendor নেই, PHP < 8.2, বা storage পারমিশন |
| 403 Forbidden | `.htaccess` ঠিকমতো আপলোড হয়েছে কি না; ডোমেইনের Document Root ঠিক ফোল্ডারে কি না |
| CSS/JS লোড হয় না | APP_URL ঠিক (https/http মিল) কি না; `php artisan config:clear` |
| ১ম পেজ ঠিক, বাকি 404 | mod_rewrite বন্ধ — হোস্টিং সাপোর্টকে জানান (সাধারণত অন থাকে) |
| `.env` বদলেও কাজ করছে না | `php artisan config:clear` অথবা `bootstrap/cache/config.php` ফাইল মুছে দিন |

## লোকালে চালানো (আগের মতোই)
```
cp .env.example .env && php artisan key:generate
php artisan migrate --seed && php artisan serve
```

## পাসওয়ার্ড রিসেট ইমেইল সেটআপ

1. cPanel → **Email Accounts** → `noreply@আপনার-ডোমেইন` একাউন্ট খুলুন।
2. `.env`-এ `MAIL_*` অংশ `.env.cpanel.example`-এর মতো বসান (`MAIL_MAILER=smtp`, পোর্ট 465, `MAIL_SCHEME=smtps`)।
3. `bootstrap/cache/config.php` থাকলে মুছে দিন, তারপর `/forgot-password` পেজে নিজের ইমেইল দিয়ে পরীক্ষা করুন।
4. মেইল না গেলে `storage/logs/laravel.log` দেখুন। ইমেইল স্প্যামে গেলে cPanel → Email Deliverability থেকে SPF/DKIM ঠিক করুন।
- লিংকের মেয়াদ ৬০ মিনিট, একবারই ব্যবহার করা যায়; ১ মিনিটে একবারের বেশি লিংক চাওয়া যায় না।

## অ্যাক্টিভিটি লগ (অ্যাডমিন → "অ্যাক্টিভিটি লগ")

phpMyAdmin → SQL ট্যাবে এটি চালান (টেবিল বানানোর পর ১০ মিনিটের মধ্যে লগ শুরু হবে):

```sql
CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  visitor VARCHAR(16) NULL,
  type VARCHAR(30) NOT NULL,
  label VARCHAR(255) NOT NULL,
  path VARCHAR(200) NULL,
  meta TEXT NULL,
  ip VARCHAR(45) NULL,
  device VARCHAR(12) NULL,
  created_at TIMESTAMP NULL,
  INDEX activity_logs_user_id_index (user_id),
  INDEX activity_logs_visitor_index (visitor),
  INDEX activity_logs_type_index (type),
  INDEX activity_logs_created_at_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## শব্দ/লাইন বাদ পড়লে কড়া নিয়ম

নতুন টেবিল লাগে না। অ্যাডমিন → সেটিংসে "শব্দ/লাইন বাদ পড়লে কড়া নিয়ম" চেকবক্স (ডিফল্ট চালু)।

> ⚠️ টেবিল আগে `utf8mb4` ছাড়া বানিয়ে থাকলে (বাংলা লেখা সেভ হয় না) এটা চালান:
> `ALTER TABLE activity_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
