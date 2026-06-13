<?php

use App\Http\Controllers\Admin\AdminsController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CvController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HostingController;
use App\Http\Controllers\Admin\MessagesController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectsController;
use App\Http\Controllers\Admin\VisitorsController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AdminInviteController;
use App\Http\Controllers\Auth\CmsGateController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CMS / Admin routes (Part 2 + Part 5)
|--------------------------------------------------------------------------
| Flow: /cms passcode gate  ->  /admin/login (email + password [+ 2FA])
|       ->  /admin dashboard (auth + cms.unlocked).
*/

// Obscurity passcode gate — reachable from the public nav.
Route::get('/cms', [CmsGateController::class, 'show'])->name('cms.gate');
Route::post('/cms', [CmsGateController::class, 'unlock'])
    ->middleware('throttle:10,1')
    ->name('cms.unlock');

// Admin invite acceptance — reachable via emailed link, no passcode/auth needed.
Route::get('admin/invite/{token}', [AdminInviteController::class, 'show'])->name('admin.invite.accept');
Route::post('admin/invite/{token}', [AdminInviteController::class, 'accept'])
    ->middleware('throttle:10,1')
    ->name('admin.invite.accept.submit');

Route::prefix('admin')->name('admin.')->middleware('cms.unlocked')->group(function () {
    // Guest (unauthenticated) admin routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])
            ->middleware('throttle:20,1')
            ->name('login.attempt');

        Route::get('2fa', [TwoFactorChallengeController::class, 'show'])->name('2fa.challenge');
        Route::post('2fa', [TwoFactorChallengeController::class, 'verify'])
            ->middleware('throttle:20,1')
            ->name('2fa.verify');
    });

    // Authenticated admin area (viewers are read-only across the whole dashboard)
    Route::middleware(['auth', 'block.viewer.writes'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Projects manager (Phase 4)
        Route::post('projects/reorder', [ProjectsController::class, 'reorder'])->name('projects.reorder');
        Route::delete('projects/bulk', [ProjectsController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
        Route::resource('projects', ProjectsController::class)->except('show');

        // Visitors + real-time notifications (Phase 6)
        Route::get('visitors/feed', [VisitorsController::class, 'feed'])->name('visitors.feed');
        Route::get('visitors/export', [VisitorsController::class, 'export'])->name('visitors.export');
        Route::post('visitors/read-all', [VisitorsController::class, 'markAllRead'])->name('visitors.read-all');
        Route::post('visitors/{visitor}/read', [VisitorsController::class, 'markRead'])->name('visitors.read');
        Route::delete('visitors', [VisitorsController::class, 'clear'])->name('visitors.clear');
        Route::get('visitors', [VisitorsController::class, 'index'])->name('visitors.index');

        // Self-hosted projects engine (Phase 8 — Multi-Project Hosting Engine)
        Route::get('hosting', [HostingController::class, 'index'])->name('hosting.index');
        Route::get('hosting/create', [HostingController::class, 'create'])->name('hosting.create');
        Route::post('hosting', [HostingController::class, 'store'])->middleware('throttle:30,1')->name('hosting.store');
        Route::get('hosting/{hosting}', [HostingController::class, 'show'])->name('hosting.show');
        Route::get('hosting/{hosting}/progress', [HostingController::class, 'progress'])->name('hosting.progress');
        Route::get('hosting/{hosting}/dump', [HostingController::class, 'exportDump'])->name('hosting.dump');
        Route::post('hosting/{hosting}/reupload', [HostingController::class, 'reuploadZip'])->middleware('throttle:30,1')->name('hosting.reupload');
        Route::post('hosting/{hosting}/reimport', [HostingController::class, 'reimportSql'])->name('hosting.reimport');
        Route::post('hosting/{hosting}/status', [HostingController::class, 'updateStatus'])->name('hosting.status');
        Route::put('hosting/{hosting}/settings', [HostingController::class, 'updateSettings'])->name('hosting.settings');
        Route::post('hosting/{hosting}/nginx', [HostingController::class, 'regenerateNginx'])->name('hosting.nginx');
        Route::post('hosting/{hosting}/to-portfolio', [HostingController::class, 'addToPortfolio'])->name('hosting.to-portfolio');
        Route::delete('hosting/{hosting}', [HostingController::class, 'destroy'])->name('hosting.destroy');

        // Messages (Phase 9)
        Route::get('messages', [MessagesController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessagesController::class, 'show'])->name('messages.show');
        Route::post('messages/{message}/reply', [MessagesController::class, 'reply'])->name('messages.reply');
        Route::patch('messages/{message}', [MessagesController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [MessagesController::class, 'destroy'])->name('messages.destroy');

        // Profile manager (Phase 9)
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        // CV manager (Phase 9)
        Route::get('cv', [CvController::class, 'index'])->name('cv.index');
        // Literal cv/pdf routes BEFORE cv/{type} so they aren't captured as a type.
        Route::post('cv/pdf', [CvController::class, 'uploadPdf'])->name('cv.pdf.upload');
        Route::delete('cv/pdf', [CvController::class, 'deletePdf'])->name('cv.pdf.delete');
        Route::post('cv/{type}', [CvController::class, 'store'])->name('cv.store');
        Route::put('cv/{type}/{id}', [CvController::class, 'update'])->name('cv.update');
        Route::delete('cv/{type}/{id}', [CvController::class, 'destroy'])->name('cv.destroy');

        // AI assistant manager — feed/tune the public assistant
        Route::get('ai', [AiSettingsController::class, 'edit'])->name('ai.edit');
        Route::put('ai', [AiSettingsController::class, 'update'])->name('ai.update');

        // Services / Skills / Testimonials manager (Premium Chunk C)
        Route::get('content', [ContentController::class, 'index'])->name('content.index');
        Route::post('content/{type}', [ContentController::class, 'store'])->name('content.store');
        Route::put('content/{type}/{id}', [ContentController::class, 'update'])->name('content.update');
        Route::delete('content/{type}/{id}', [ContentController::class, 'destroy'])->name('content.destroy');

        // Blog manager (Phase 9)
        Route::post('blog/categories', [BlogController::class, 'storeCategory'])->name('blog.categories.store');
        Route::delete('blog/categories/{category}', [BlogController::class, 'destroyCategory'])->name('blog.categories.destroy');
        Route::patch('blog/comments/{comment}/approve', [BlogController::class, 'approveComment'])->name('blog.comments.approve');
        Route::delete('blog/comments/{comment}', [BlogController::class, 'destroyComment'])->name('blog.comments.destroy');
        Route::resource('blog', BlogController::class)->parameters(['blog' => 'post'])->except('show');

        // Multi-admin management (Phase 9) — super admins only
        Route::middleware('role:super_admin')->group(function () {
            Route::get('admins', [AdminsController::class, 'index'])->name('admins.index');
            Route::post('admins/create', [AdminsController::class, 'store'])->name('admins.store');
            Route::put('admins/passcode', [AdminsController::class, 'updatePasscode'])->name('admins.passcode');
            Route::post('admins/invite', [AdminsController::class, 'invite'])->name('admins.invite');
            Route::delete('admins/invite/{invite}', [AdminsController::class, 'cancelInvite'])->name('admins.invite.cancel');
            Route::patch('admins/{admin}/role', [AdminsController::class, 'updateRole'])->name('admins.role');
            Route::delete('admins/{admin}/revoke', [AdminsController::class, 'revoke'])->name('admins.revoke');
            Route::patch('admins/{id}/restore', [AdminsController::class, 'restore'])->name('admins.restore');
            Route::get('admins/{id}/activity', [AdminsController::class, 'activity'])->name('admins.activity');
        });
    });
});
