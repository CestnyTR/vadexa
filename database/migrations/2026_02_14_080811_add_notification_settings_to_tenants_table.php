<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('notification_time', 5)->default('10:00');
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('mail_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['notification_time', 'sms_enabled', 'mail_enabled']);
        });
    }
};
