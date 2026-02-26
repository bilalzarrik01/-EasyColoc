<?php

use App\Http\Controllers\ColocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('colocations', ColocationController::class)->except(['destroy']);
    Route::patch('colocations/{colocation}/cancel', [ColocationController::class, 'cancel'])->name('colocations.cancel');
    Route::patch('colocations/{colocation}/leave', [ColocationController::class, 'leave'])->name('colocations.leave');
    Route::patch('colocations/{colocation}/members/{member}/remove', [ColocationController::class, 'removeMember'])
        ->name('colocations.removeMember');
    Route::post('colocations/{colocation}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::post('colocations/{colocation}/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('colocations/{colocation}/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('colocations/{colocation}/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('colocations/{colocation}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::patch(
        'colocations/{colocation}/settlements/{settlement}/mark-paid',
        [SettlementController::class, 'markPaid']
    )->name('settlements.markPaid');

    Route::get('invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::patch('invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::patch('invitations/{token}/refuse', [InvitationController::class, 'refuse'])->name('invitations.refuse');
});

require __DIR__.'/auth.php';
