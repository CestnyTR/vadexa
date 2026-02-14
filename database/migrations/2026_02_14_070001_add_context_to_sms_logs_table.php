<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->string('context_type', 64)->nullable()->after('sent_at');
            $table->unsignedBigInteger('context_id')->nullable()->after('context_type');

            $table->index(['tenant_id', 'context_type', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'context_type', 'context_id']);
            $table->dropColumn(['context_type', 'context_id']);
        });
    }
};
