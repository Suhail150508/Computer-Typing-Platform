<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeedbackReportController as AdminFeedbackReportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\Admin\LabController as AdminLabController;
use App\Http\Controllers\Admin\PassageController as AdminPassageController;
use App\Http\Controllers\Admin\PaymentSubmissionController as AdminPaymentSubmissionController;
use App\Http\Controllers\Admin\PracticeCardController as AdminPracticeCardController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SubscriptionPlanController as AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\TypingAttemptController as AdminTypingAttemptController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TipsController;
use App\Http\Controllers\TypingTestController;
use App\Http\Controllers\WpmViewerController;
use Illuminate\Support\Facades\Route;

// লগইন করা থাকলে ড্যাশবোর্ডে, না থাকলে সরাসরি লগইনে — একটা মাত্র রিডাইরেক্টে (SEO ও স্পিডের জন্য ভালো)
Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

// SEO: robots.txt ও sitemap.xml (পাবলিক, কোনো মিডলওয়্যার ছাড়াই)
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.attempt');

    // পাসওয়ার্ড রিসেট (ইমেইলে লিংক)
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:10,1')->name('password.update');

    // Google দিয়ে লগইন
    Route::prefix('auth/google')->name('auth.google.')->group(function () {
        Route::get('/redirect', [AuthController::class, 'googleRedirect'])->name('redirect');
        Route::get('/callback', [AuthController::class, 'googleCallback'])->name('callback');
    });
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/feedback/token', [FeedbackController::class, 'token'])->name('feedback.token');
    Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:10,1')->name('feedback.store');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // টাইপিং টেস্ট
    Route::prefix('typing')->name('typing.')->group(function () {
        Route::get('/setup/{lab}', [TypingTestController::class, 'setup'])->name('setup');
        Route::post('/start', [TypingTestController::class, 'start'])->name('start');
        Route::get('/test', [TypingTestController::class, 'test'])->name('test');
        Route::post('/submit', [TypingTestController::class, 'submit'])->name('submit');
    });

    // অনুশীলন মোড: টাইমার ও পাস/ফেল ছাড়া পাঠভিত্তিক শিক্ষা (কিছু সেভ হয় না)
    Route::get('/learn', [LearnController::class, 'index'])->name('learn.index');
    Route::post('/learn/{lesson}/complete', [LearnController::class, 'complete'])->where('lesson', '[a-z]{2}-[0-9]+')->middleware('throttle:60,1')->name('learn.complete');
    Route::get('/learn/{lesson}', [LearnController::class, 'show'])->where('lesson', '[a-z]{2}-[0-9]+')->name('learn.show');

    // টাইপিং শুরু (Basic → Advance → Hard progression)
    Route::prefix('typing-start')->name('practice.')->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
        Route::get('/card/{card}/preview', [PracticeController::class, 'preview'])->whereNumber('card')->name('preview');
        Route::post('/card/{card}/start', [PracticeController::class, 'start'])->whereNumber('card')->name('start');
        Route::get('/test', [PracticeController::class, 'test'])->name('test');
        Route::post('/submit', [PracticeController::class, 'submit'])->name('submit');
    });

    // শীট/পেপার দেখে টাইপ (বাংলা ও English) — ফ্রি প্র্যাকটিস, সেভ হয় না
    Route::view('/free-typing', 'free.index')->name('free.index');

    // টাইপিং স্পিড বাড়ানোর দিকনির্দেশনা (স্ট্যাটিক গাইড)
    Route::get('/typing-tips', [TipsController::class, 'index'])->name('tips.index');

    // WPM Viewer — সময়ের সাথে উন্নতির গ্রাফ ও পরিসংখ্যান
    Route::get('/wpm-viewer', [WpmViewerController::class, 'index'])->name('wpm.index');

    // রেজাল্ট
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/', [ResultController::class, 'index'])->name('index');
        Route::delete('/', [ResultController::class, 'destroyAll'])->name('destroyAll');
        Route::get('/{attempt}', [ResultController::class, 'show'])->whereNumber('attempt')->name('show');
        Route::delete('/{attempt}', [ResultController::class, 'destroy'])->whereNumber('attempt')->name('destroy');
    });

    // প্রোফাইল ও লেনদেনের ইতিহাস
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
        Route::delete('/avatar', [ProfileController::class, 'removeAvatar'])->name('avatar.remove');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });
    Route::get('/transactions', [ProfileController::class, 'transactions'])->name('transactions.index');

    // প্রিমিয়াম/একাউন্ট অ্যাক্টিভেশন (ম্যানুয়াল bKash/Nagad/Rocket পেমেন্ট)
    Route::prefix('premium')->name('premium.')->group(function () {
        Route::get('/', [PremiumController::class, 'index'])->name('index');
        Route::get('/{plan}', [PremiumController::class, 'show'])->whereNumber('plan')->name('show');
        Route::post('/{plan}/submit', [PremiumController::class, 'submit'])->whereNumber('plan')->name('submit');
    });
});

// ==================== অ্যাডমিন প্যানেল ====================
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('labs', AdminLabController::class)->except('show');
    Route::resource('passages', AdminPassageController::class)->except('show');
    Route::post('practice-cards/bulk-duration', [AdminPracticeCardController::class, 'bulkDuration'])->name('practice-cards.bulkDuration');
    Route::resource('practice-cards', AdminPracticeCardController::class)->except('show');
    Route::resource('subscription-plans', AdminSubscriptionPlanController::class)->except('show');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->whereNumber('user')->name('users.show');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggleActive');
    Route::post('/users/{user}/wallet', [AdminUserController::class, 'adjustWallet'])->name('users.wallet');
    Route::post('/users/{user}/subscription', [AdminUserController::class, 'extendSubscription'])->name('users.subscription');
    Route::post('/users/{user}/subscription/reduce', [AdminUserController::class, 'reduceSubscription'])->name('users.subscription.reduce');

    Route::get('/wallets', [AdminWalletController::class, 'index'])->name('wallets.index');

    Route::get('/attempts', [AdminTypingAttemptController::class, 'index'])->name('attempts.index');
    Route::get('/attempts/{attempt}', [AdminTypingAttemptController::class, 'show'])->whereNumber('attempt')->name('attempts.show');
    Route::delete('/attempts/{attempt}', [AdminTypingAttemptController::class, 'destroy'])->whereNumber('attempt')->name('attempts.destroy');

    Route::get('/activity', [\App\Http\Controllers\Admin\ActivityController::class, 'index'])->name('activity.index');
    Route::post('/activity/test', [\App\Http\Controllers\Admin\ActivityController::class, 'test'])->name('activity.test');
    Route::post('/activity/prune', [\App\Http\Controllers\Admin\ActivityController::class, 'prune'])->name('activity.prune');
    Route::get('/feedback', [AdminFeedbackReportController::class, 'index'])->name('feedback.index');
    Route::patch('/feedback/{feedback}', [AdminFeedbackReportController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{feedback}', [AdminFeedbackReportController::class, 'destroy'])->name('feedback.destroy');

    Route::get('/payments', [AdminPaymentSubmissionController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/approve', [AdminPaymentSubmissionController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [AdminPaymentSubmissionController::class, 'reject'])->name('payments.reject');

    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});
