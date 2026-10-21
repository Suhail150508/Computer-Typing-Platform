<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * robots.txt ডায়নামিকভাবে বানানো হয় যাতে .env এর APP_URL থেকে সঠিক ডোমেইন
     * নিজে থেকেই বসে — ডোমেইন বদলালে কোথাও হাত দিতে হয় না।
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /login',
            'Allow: /register',
            'Disallow: /dashboard',
            'Disallow: /typing',
            'Disallow: /typing-start',
            'Disallow: /typing-tips',
            'Disallow: /wpm-viewer',
            'Disallow: /results',
            'Disallow: /premium',
            'Disallow: /admin',
            'Disallow: /auth/google',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }

    /**
     * sitemap.xml — এই মুহূর্তে পাবলিক (লগইন ছাড়া দেখা যায়) পেজ মাত্র দুটো:
     * লগইন ও রেজিস্টার। ভবিষ্যতে পাবলিক ল্যান্ডিং/ব্লগ পেজ যোগ হলে এখানেই যোগ করুন।
     */
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('login'), 'priority' => '1.0', 'changefreq' => 'monthly'],
            ['loc' => route('register'), 'priority' => '0.9', 'changefreq' => 'monthly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'."\n";
            $xml .= '    <changefreq>'.$u['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$u['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
