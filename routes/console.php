<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Secret;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $expiredSecrets = Secret::where('expiry_date', '<', now())->get();
    
    foreach ($expiredSecrets as $secret) {
        if (!empty($secret->file_paths) && is_array($secret->file_paths)) {
            foreach ($secret->file_paths as $path) {
                Storage::disk('s3')->delete($path);
            }
        }
        $secret->delete();
    }
})->daily()->name('cleanup_expired_secrets')->withoutOverlapping();
