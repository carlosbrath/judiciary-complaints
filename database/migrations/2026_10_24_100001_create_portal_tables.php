<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Courts of the lower judiciary of AJ&K, grouped by district.
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ur')->nullable();
            $table->string('district_en', 100);
            $table->string('district_ur', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Overseas citizens. Accounts opened by the chatbot have no email/password until the citizen registers.
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('country', 2); // ISO 3166-1 alpha-2, see App\Support\Countries
            $table->string('phone', 20); // WhatsApp, international format (+447700900123)
            $table->string('cnic_passport', 20)->unique(); // 12345-1234567-1 or a passport number
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 20)->nullable()->unique();
            $table->foreignId('citizen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained();
            $table->string('case_number', 50);
            $table->string('case_title', 200);
            $table->string('category', 30); // App\Enums\ComplaintCategory
            $table->text('description');
            $table->string('status', 20)->default('submitted')->index(); // App\Enums\ComplaintStatus
            $table->string('submitted_via', 10)->default('form'); // form | chatbot
            $table->string('ip', 45)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('complaint_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();
        });

        // Admin follow-ups: a remark, a status change, or both. Citizens see these read-only.
        Schema::create('complaint_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status_from', 20)->nullable();
            $table->string('status_to', 20)->nullable();
            $table->text('body')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('complaint_reviews');
        Schema::dropIfExists('complaint_attachments');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('citizens');
        Schema::dropIfExists('courts');
    }
};
