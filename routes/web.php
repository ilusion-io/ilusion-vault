<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('/secret/{id}', function ($id) {
    return redirect()->route('home', ['secret' => $id]);
})->name('secrets.view');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('profile', function () {
        $secrets = \App\Models\Secret::where('user_id', auth()->id())
            ->where('expiry_date', '>', now())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($secret) {
                return [
                    'secret_id' => $secret->secret_id,
                    'url' => url('/secret/' . $secret->secret_id),
                    'expiry_date' => $secret->expiry_date->toIso8601String(),
                    'burn_on_read' => $secret->burn_on_read,
                    'recipient_email' => $secret->recipient_email,
                    'created_at' => $secret->created_at->toIso8601String(),
                ];
            });

        return inertia('Profile', [
            'secrets' => $secrets,
        ]);
    })->name('profile');
});

use App\Http\Controllers\SecretController;

Route::post('/api/secrets', [SecretController::class, 'store'])->name('secrets.store');
Route::get('/api/secrets/{secretId}', [SecretController::class, 'show'])->name('secrets.show');
Route::get('/api/secrets/file/download', [SecretController::class, 'downloadFile'])
    ->name('secrets.file.download')
    ->middleware('signed');

require __DIR__.'/settings.php';
