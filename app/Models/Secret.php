<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secret extends Model
{
    protected $fillable = [
        'secret_id',
        'identifier',
        'encrypted_payload',
        'expiry_date',
        'burn_on_read',
        'recipient_email',
        'encryption_hint',
        'file_paths',
        'user_id',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'burn_on_read' => 'boolean',
        'file_paths' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
