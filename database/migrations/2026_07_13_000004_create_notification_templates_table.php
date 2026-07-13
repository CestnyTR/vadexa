<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('channel'); // sms | mail
            $table->string('type');    // upcoming | due_today | overdue
            $table->text('body');
            $table->timestamps();

            $table->unique(['tenant_id', 'channel', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
