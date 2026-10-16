<?php

declare(strict_types=1);

use Mwambe\Controllers\Admin\AuditLogController;
use Mwambe\Controllers\Admin\AuthController;
use Mwambe\Controllers\Admin\DashboardController;
use Mwambe\Controllers\Admin\DocumentController;
use Mwambe\Controllers\Admin\EnquiryController;
use Mwambe\Controllers\Admin\EventController;
use Mwambe\Controllers\Admin\HomepageSlideController;
use Mwambe\Controllers\Admin\ImpactController;
use Mwambe\Controllers\Admin\MediaController;
use Mwambe\Controllers\Admin\NewsController;
use Mwambe\Controllers\Admin\PasswordResetController;
use Mwambe\Controllers\Admin\ProfileController;
use Mwambe\Controllers\Admin\ProgrammeController;
use Mwambe\Controllers\Admin\SectorController;
use Mwambe\Controllers\Admin\ServiceController;
use Mwambe\Controllers\Admin\UserController;
use Mwambe\Core\Http\Middleware\AuthMiddleware;
use Mwambe\Core\Http\Middleware\CsrfMiddleware;
use Mwambe\Core\Http\Middleware\RoleMiddleware;

return function (Mwambe\Core\Http\Router $router): void {
    // ---------- Public auth routes ----------
    $router->get('/admin/login',           [AuthController::class, 'showLogin'],  [CsrfMiddleware::class]);
    $router->post('/admin/login',          [AuthController::class, 'login'],      [CsrfMiddleware::class]);
    $router->get('/admin/forgot-password', [PasswordResetController::class, 'showRequest'], [CsrfMiddleware::class]);
    $router->post('/admin/forgot-password',[PasswordResetController::class, 'sendRequest'], [CsrfMiddleware::class]);
    $router->get('/admin/reset-password',  [PasswordResetController::class, 'showReset'],   [CsrfMiddleware::class]);
    $router->post('/admin/reset-password', [PasswordResetController::class, 'complete'],    [CsrfMiddleware::class]);

    // ---------- Authenticated (any role) ----------
    $protected = [CsrfMiddleware::class, AuthMiddleware::class];

    $router->get('/admin',              [DashboardController::class, 'index'], $protected);
    $router->post('/admin/logout',      [AuthController::class, 'logout'],     $protected);

    $router->get('/admin/profile',          [ProfileController::class, 'show'],           $protected);
    $router->post('/admin/profile/password',[ProfileController::class, 'changePassword'], $protected);

    // ---------- Enquiries ----------
    $enquiriesOnly = [CsrfMiddleware::class, AuthMiddleware::class, [RoleMiddleware::class, ['super_admin', 'admin', 'enquiries']]];

    $router->get('/admin/enquiries/contact',                    [EnquiryController::class, 'contact'],         $enquiriesOnly);
    $router->get('/admin/enquiries/contact/{id}',               [EnquiryController::class, 'contactShow'],     $enquiriesOnly);
    $router->post('/admin/enquiries/contact/{id}/edit',         [EnquiryController::class, 'contactUpdate'],   $enquiriesOnly);
    $router->post('/admin/enquiries/contact/{id}/retry-email',  [EnquiryController::class, 'contactRetryEmail'], $enquiriesOnly);

    $router->get('/admin/enquiries/partnership',                [EnquiryController::class, 'partnership'],         $enquiriesOnly);
    $router->get('/admin/enquiries/partnership/{id}',           [EnquiryController::class, 'partnershipShow'],     $enquiriesOnly);
    $router->post('/admin/enquiries/partnership/{id}/edit',     [EnquiryController::class, 'partnershipUpdate'],   $enquiriesOnly);

    // ---------- Content editors: super_admin, admin, editor ----------
    $editors = [CsrfMiddleware::class, AuthMiddleware::class, [RoleMiddleware::class, ['super_admin', 'admin', 'editor']]];

    // Homepage slides
    $router->get('/admin/homepage/slides',            [HomepageSlideController::class, 'index'],  $editors);
    $router->get('/admin/homepage/slides/{id}/edit',  [HomepageSlideController::class, 'edit'],   $editors);
    $router->post('/admin/homepage/slides/{id}/edit', [HomepageSlideController::class, 'update'], $editors);

    // Programmes
    $router->get('/admin/programmes',              [ProgrammeController::class, 'index'],  $editors);
    $router->get('/admin/programmes/new',          [ProgrammeController::class, 'create'], $editors);
    $router->post('/admin/programmes',             [ProgrammeController::class, 'store'],  $editors);
    $router->get('/admin/programmes/{id}/edit',    [ProgrammeController::class, 'edit'],   $editors);
    $router->post('/admin/programmes/{id}/edit',   [ProgrammeController::class, 'update'], $editors);
    $router->post('/admin/programmes/{id}/delete', [ProgrammeController::class, 'delete'], $editors);

    // Impact
    $router->get('/admin/impact',              [ImpactController::class, 'index'],  $editors);
    $router->get('/admin/impact/new',          [ImpactController::class, 'create'], $editors);
    $router->post('/admin/impact',             [ImpactController::class, 'store'],  $editors);
    $router->get('/admin/impact/{id}/edit',    [ImpactController::class, 'edit'],   $editors);
    $router->post('/admin/impact/{id}/edit',   [ImpactController::class, 'update'], $editors);

    // Sectors
    $router->get('/admin/sectors',                 [SectorController::class, 'index'],  $editors);
    $router->get('/admin/sectors/new',             [SectorController::class, 'create'], $editors);
    $router->post('/admin/sectors',                [SectorController::class, 'store'],  $editors);
    $router->get('/admin/sectors/{id}/edit',       [SectorController::class, 'edit'],   $editors);
    $router->post('/admin/sectors/{id}/edit',      [SectorController::class, 'update'], $editors);
    $router->post('/admin/sectors/{id}/delete',    [SectorController::class, 'delete'], $editors);

    // Services
    $router->get('/admin/services',                [ServiceController::class, 'index'],  $editors);
    $router->get('/admin/services/new',            [ServiceController::class, 'create'], $editors);
    $router->post('/admin/services',               [ServiceController::class, 'store'],  $editors);
    $router->get('/admin/services/{id}/edit',      [ServiceController::class, 'edit'],   $editors);
    $router->post('/admin/services/{id}/edit',     [ServiceController::class, 'update'], $editors);
    $router->post('/admin/services/{id}/delete',   [ServiceController::class, 'delete'], $editors);

// News — categories routes MUST come before {id}/edit
    $router->get('/admin/news',                              [NewsController::class, 'index'],            $editors);
    $router->get('/admin/news/new',                          [NewsController::class, 'create'],           $editors);
    $router->post('/admin/news',                             [NewsController::class, 'store'],            $editors);
    $router->get('/admin/news/categories',                   [NewsController::class, 'categoriesIndex'],  $editors);
    $router->post('/admin/news/categories',                  [NewsController::class, 'categoriesStore'],  $editors);
    $router->get('/admin/news/categories/{id}/edit',         [NewsController::class, 'categoriesEdit'],   $editors);
    $router->post('/admin/news/categories/{id}/edit',        [NewsController::class, 'categoriesUpdate'], $editors);
    $router->post('/admin/news/categories/{id}/delete',      [NewsController::class, 'categoriesDelete'], $editors);
    $router->get('/admin/news/{id}/edit',                    [NewsController::class, 'edit'],             $editors);
    $router->post('/admin/news/{id}/edit',                   [NewsController::class, 'update'],           $editors);
    $router->post('/admin/news/{id}/delete',                 [NewsController::class, 'delete'],           $editors);

    // Events
    $router->get('/admin/events',              [EventController::class, 'index'],  $editors);
    $router->get('/admin/events/new',          [EventController::class, 'create'], $editors);
    $router->post('/admin/events',             [EventController::class, 'store'],  $editors);
    $router->get('/admin/events/{id}/edit',    [EventController::class, 'edit'],   $editors);
    $router->post('/admin/events/{id}/edit',   [EventController::class, 'update'], $editors);
    $router->post('/admin/events/{id}/delete', [EventController::class, 'delete'], $editors);

    // Media library
    $router->get('/admin/media',              [MediaController::class, 'index'],  $editors);
    $router->post('/admin/media',             [MediaController::class, 'store'],  $editors);
    $router->post('/admin/media/{id}/edit',   [MediaController::class, 'update'], $editors);
    $router->post('/admin/media/{id}/delete', [MediaController::class, 'delete'], $editors);

    // Documents
    $router->get('/admin/documents',              [DocumentController::class, 'index'],  $editors);
    $router->post('/admin/documents',             [DocumentController::class, 'store'],  $editors);
    $router->post('/admin/documents/{id}/delete', [DocumentController::class, 'delete'], $editors);

    // ---------- Admin-only ----------
    $adminOnly = [CsrfMiddleware::class, AuthMiddleware::class, [RoleMiddleware::class, ['super_admin', 'admin']]];

    $router->get('/admin/users',                    [UserController::class, 'index'],       $adminOnly);
    $router->get('/admin/users/new',                [UserController::class, 'create'],      $adminOnly);
    $router->post('/admin/users',                   [UserController::class, 'store'],       $adminOnly);
    $router->get('/admin/users/{id}/edit',          [UserController::class, 'edit'],        $adminOnly);
    $router->post('/admin/users/{id}/edit',         [UserController::class, 'update'],      $adminOnly);
    $router->post('/admin/users/{id}/force-logout', [UserController::class, 'forceLogout'], $adminOnly);
    $router->post('/admin/users/{id}/send-reset',   [UserController::class, 'sendReset'],   $adminOnly);

    $router->get('/admin/system/audit', [AuditLogController::class, 'index'], $adminOnly);
};