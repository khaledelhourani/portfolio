<?php

use App\Http\Controllers\Auth\MemberAuthController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Public\AiAssistantController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\CommentController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FavoriteController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\HostedController;
use App\Http\Controllers\Public\LanguageController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
| Home hosts the Hero + CV + Contact sections (Part 1). Projects, Blog and
| the AI Assistant pages are added in later phases. Admin/CMS routes live in
| routes/admin.php.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Live "online now" counter — polled by the public footer (Premium Chunk D).
Route::get('/api/online', [StatsController::class, 'online'])
    ->middleware('throttle:60,1')
    ->name('api.online');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,60') // 5 submissions / hour (anti-spam)
    ->name('contact.store');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{post}/comments', [CommentController::class, 'store'])
    ->middleware(['auth:member', 'throttle:10,1'])
    ->name('blog.comments.store');

Route::get('/ai-assistant', [AiAssistantController::class, 'show'])->name('ai.assistant');
Route::post('/ai-assistant/chat', [AiAssistantController::class, 'chat'])
    ->middleware('throttle:30,1') // burst guard; daily 20/IP cap enforced in controller
    ->name('ai.chat');

/*
| Member social authentication (Part 5 — Laravel Socialite).
*/
Route::middleware('guest:member')->group(function () {
    // Email + password
    Route::get('/login', [MemberAuthController::class, 'showLogin'])->name('member.login');
    Route::post('/login', [MemberAuthController::class, 'login'])->middleware('throttle:10,1')->name('member.login.attempt');
    Route::get('/register', [MemberAuthController::class, 'showRegister'])->name('member.register');
    Route::post('/register', [MemberAuthController::class, 'register'])->middleware('throttle:10,1')->name('member.register.attempt');

    // Social (optional)
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('member.oauth.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('member.oauth.callback');
});

Route::middleware('auth:member')->group(function () {
    Route::post('/logout', [SocialiteController::class, 'logout'])->name('member.logout');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/projects/{project}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Multi-Project Hosting Engine — public serving
|--------------------------------------------------------------------------
| Catch-all under /hosted/{slug}. Declared LAST so it never shadows the CMS
| routes above. On the nginx container, `location /hosted/{slug}/…\.php` is
| handled by php-fpm before reaching Laravel; this route serves static assets
| everywhere and PHP projects locally (via php-cgi).
*/
Route::get('/hosted/{slug}/{path?}', [HostedController::class, 'serve'])
    ->where('path', '.*')
    ->name('hosted.serve');
Route::match(['post', 'put', 'patch', 'delete'], '/hosted/{slug}/{path?}', [HostedController::class, 'serve'])
    ->where('path', '.*')
    ->name('hosted.serve.write');
