<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('secrets', function (Blueprint $table) {
            $table->id();
            $table->string('secret_id')->unique();
            $table->longText('encrypted_payload');
            $table->dateTime('expiry_date');
            $table->boolean('burn_on_read')->default(false);
            $table->string('recipient_email')->nullable();
            $table->string('encryption_hint')->nullable();
            $table->json('file_paths')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secrets');
    }
};
