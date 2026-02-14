<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->string('recurrence_type', 16)->default('once')->after('description');
            $table->unsignedSmallInteger('installment_count')->nullable()->after('recurrence_type');
            $table->unsignedSmallInteger('installment_number')->nullable()->after('installment_count');
            $table->foreignId('parent_id')->nullable()->after('installment_number')
                  ->constrained('receivables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['recurrence_type', 'installment_count', 'installment_number', 'parent_id']);
        });
    }
};
