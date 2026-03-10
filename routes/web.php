<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\AgreementDirectionController;
use App\Http\Controllers\AgreementTypeController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\OrganizationTypeController;
use App\Http\Controllers\PartnerContactController;
use App\Http\Controllers\PartnerOrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
    Route::put('role-permissions/{role}', [RolePermissionController::class, 'update'])->name('role-permissions.update');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('ranks', RankController::class)->except(['show']);
    Route::resource('countries', CountryController::class)->except(['show']);
    Route::resource('documents', DocumentController::class)->except(['show']);
    Route::resource('document-types', DocumentTypeController::class)->except(['show']);
    Route::resource('events', EventController::class)->except(['show']);
    Route::resource('event-types', EventTypeController::class)->except(['show']);
    Route::resource('agreements', AgreementController::class)->except(['show']);
    Route::resource('agreement-directions', AgreementDirectionController::class)->except(['show']);
    Route::resource('agreement-types', AgreementTypeController::class)->except(['show']);
    Route::resource('organization-types', OrganizationTypeController::class)->except(['show']);
    Route::resource('partner-organizations', PartnerOrganizationController::class)->except(['show']);
    Route::resource('partner-contacts', PartnerContactController::class)->except(['show']);
    Route::resource('visits', VisitController::class)->except(['show']);
    Route::resource('visit-types', VisitTypeController::class)->except(['show']);
});

require __DIR__.'/auth.php';
