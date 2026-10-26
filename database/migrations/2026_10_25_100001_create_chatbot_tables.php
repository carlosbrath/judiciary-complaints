<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Questions the chatbot asks when filing a complaint, in order. Core rows fill a complaint or
        // citizen field (key); custom rows are extra questions added by the admin.
        Schema::create('chatbot_questions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('type', 30); // see ChatbotQuestion::TYPE_*
            $table->text('question_en');
            $table->text('question_ur')->nullable();
            $table->string('help_en', 255)->nullable();
            $table->string('help_ur', 255)->nullable();
            $table->json('options')->nullable(); // custom "choice" questions: [{en, ur}]
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Answers for "How it works / FAQs"; keywords help match typed questions.
        Schema::create('chatbot_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question_en', 255);
            $table->string('question_ur', 255)->nullable();
            $table->text('answer_en');
            $table->text('answer_ur')->nullable();
            $table->string('keywords', 255)->nullable(); // comma-separated, any language
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // One row per chat, from the first message the citizen sends.
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->index(); // browsing | filing | review | completed | restarted
            $table->string('locale', 5)->nullable();
            $table->string('current_question', 50)->nullable(); // where an unfinished filing stopped
            $table->unsignedSmallInteger('message_count')->default(0);
            // Full chat state (answers incl. CNIC/phone) so the chat resumes across pages and admins can follow up — encrypted.
            $table->longText('state')->nullable();
            $table->foreignId('citizen_id')->nullable()->constrained()->nullOnDelete(); // logged-in citizen, or the account a complaint was filed under
            $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Transcript. CNIC/passport and phone numbers are masked before they are stored here.
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('sender', 5); // bot | user
            $table->text('body');
            $table->string('tone', 10)->nullable(); // error | success
            $table->string('question_key', 50)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('chatbot_faqs');
        Schema::dropIfExists('chatbot_questions');
    }
};
