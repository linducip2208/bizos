<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email', 255);
            $table->string('name', 255)->nullable();
            $table->string('provider', 50)->default('custom_imap');
            $table->string('imap_host', 255)->nullable();
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption', 10)->default('ssl');
            $table->string('smtp_host', 255)->nullable();
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption', 10)->default('tls');
            $table->text('password_encrypted');
            $table->string('signature', 5000)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_fetch')->default(true);
            $table->integer('fetch_interval_minutes')->default(5);
            $table->timestamp('last_fetched_at')->nullable();
            $table->string('last_error', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('email_accounts')->cascadeOnDelete();
            $table->string('message_uid', 255);
            $table->string('message_id', 500)->nullable();
            $table->string('from_email', 255);
            $table->string('from_name', 255)->nullable();
            $table->string('to_email', 500);
            $table->string('cc', 500)->nullable();
            $table->string('bcc', 500)->nullable();
            $table->string('subject', 1000)->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('folder', 50)->default('INBOX');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->boolean('has_attachments')->default(false);
            $table->timestamp('email_date')->nullable();
            $table->string('in_reply_to', 500)->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['email_account_id', 'message_uid', 'folder']);
            $table->index('from_email');
            $table->index('email_date');
            $table->index('folder');
            $table->index('is_read');
            $table->index('is_starred');
            $table->fullText(['subject', 'body_text', 'from_email', 'from_name', 'to_email'], 'email_msgs_fulltext');
        });

        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_message_id')->constrained('email_messages')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('storage_path', 500)->nullable();
            $table->string('content_id', 255)->nullable();
            $table->boolean('is_inline')->default(false);
            $table->timestamps();
        });

        Schema::create('email_links', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 500);
            $table->string('linkable_type', 100);
            $table->unsignedBigInteger('linkable_id');
            $table->string('link_reason', 255)->nullable();
            $table->foreignId('linked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['message_id']);
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_links');
        Schema::dropIfExists('email_attachments');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_accounts');
    }
};
