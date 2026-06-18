<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Secret;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecretSent;

class SecretController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
            'expiry' => 'required|string',
            'burn_on_read' => 'boolean',
            'custom_address' => 'nullable|string|unique:secrets,secret_id',
            'recipient_email' => ['nullable', 'string', function ($attribute, $value, $fail) {
                $emails = array_filter(array_map('trim', explode(',', $value)));
                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail("The {$attribute} contains an invalid email address: {$email}.");
                    }
                }
            }],
            'encryption_hint' => 'nullable|string',
            'files.*' => 'nullable|file',
            'file_metadata' => 'nullable|string',
        ]);

        $expiryDate = Carbon::now();
        switch ($request->expiry) {
            case 'Never':
            case 'No Expiry':
                $expiryDate->addYears(100);
                break;
            case '15 Days':
                $expiryDate->addDays(15);
                break;
            case '7 Days':
                $expiryDate->addDays(7);
                break;
            case '1 Day':
                $expiryDate->addDays(1);
                break;
            case '1 Hour':
                $expiryDate->addHour();
                break;
            default:
                $expiryDate->addDays(7);
        }

        $secretId = $request->custom_address ?: Str::random(10);
        
        if (!$request->custom_address) {
            while (Secret::where('secret_id', $secretId)->exists()) {
                $secretId = Str::random(10);
            }
        }

        $filePaths = [];
        $metadata = [];
        if ($request->input('file_metadata')) {
            $metadata = json_decode($request->input('file_metadata'), true) ?? [];
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                // Store file directly inside secrets/ directory using a random hash (without secretId subfolder)
                $path = $file->store('secrets', 'r2');
                
                $filePaths[] = [
                    'path' => $path,
                    'encrypted_metadata' => $metadata[$index]['encrypted_metadata'] ?? '',
                    'salt' => $metadata[$index]['salt'] ?? '',
                    'iv' => $metadata[$index]['iv'] ?? '',
                ];
            }
        }

        $user = auth()->guard('sanctum')->user() ?? auth()->user();

        $secret = Secret::create([
            'secret_id' => $secretId,
            'encrypted_payload' => $request->payload,
            'expiry_date' => $expiryDate,
            'burn_on_read' => $request->burn_on_read ?? false,
            'recipient_email' => $request->recipient_email,
            'encryption_hint' => $request->encryption_hint,
            'file_paths' => $filePaths,
            'user_id' => $user ? $user->id : null,
        ]);

        $secretUrl = url('/secret/' . $secret->secret_id);

        if ($request->recipient_email) {
            $emails = array_filter(array_map('trim', explode(',', $request->recipient_email)));
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($email)->queue(
                        new SecretSent($secretUrl, $request->encryption_hint, $user ? $user->name : null)
                    );
                }
            }
        }

        return response()->json([
            'secret_id' => $secret->secret_id,
            'url' => $secretUrl,
        ]);
    }

    public function show($secretId)
    {
        $secret = Secret::where('secret_id', $secretId)->first();

        if (!$secret || $secret->expiry_date < Carbon::now()) {
            if ($secret) {
                // Cleanup R2 files
                if ($secret->file_paths) {
                    foreach ($secret->file_paths as $file) {
                        if (is_array($file) && isset($file['path'])) {
                            Storage::disk('r2')->delete($file['path']);
                        } elseif (is_string($file)) {
                            Storage::disk('r2')->delete($file);
                        }
                    }
                }
                $secret->delete();
            }
            return response()->json(['message' => 'Secret not found or expired.'], 404);
        }

        $filePaths = [];
        if ($secret->file_paths) {
            foreach ($secret->file_paths as $file) {
                if (is_array($file)) {
                    $filePaths[] = [
                        'download_url' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'secrets.file.download',
                            now()->addMinutes(15),
                            [
                                'path' => $file['path'],
                                'burn' => $secret->burn_on_read ? '1' : '0'
                            ]
                        ),
                        'encrypted_metadata' => $file['encrypted_metadata'],
                        'salt' => $file['salt'],
                        'iv' => $file['iv'],
                    ];
                } else {
                    // Fallback for legacy simple string paths
                    $filePaths[] = [
                        'download_url' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'secrets.file.download',
                            now()->addMinutes(15),
                            [
                                'path' => $file,
                                'burn' => $secret->burn_on_read ? '1' : '0'
                            ]
                        ),
                        'encrypted_metadata' => '',
                        'salt' => '',
                        'iv' => '',
                    ];
                }
            }
        }

        $payload = [
            'encrypted_payload' => $secret->encrypted_payload,
            'encryption_hint' => $secret->encryption_hint,
            'file_paths' => $filePaths,
            'burn_on_read' => $secret->burn_on_read,
        ];

        if ($secret->burn_on_read) {
            $secret->delete();
        }

        return response()->json($payload);
    }

    public function downloadFile(Request $request)
    {
        $path = $request->input('path');
        $burn = $request->input('burn');

        if (!Storage::disk('r2')->exists($path)) {
            abort(404, 'File not found or already deleted.');
        }

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
        ];

        $content = Storage::disk('r2')->get($path);

        if ($burn == '1') {
            Storage::disk('r2')->delete($path);
        }

        return response($content, 200, $headers);
    }
}
