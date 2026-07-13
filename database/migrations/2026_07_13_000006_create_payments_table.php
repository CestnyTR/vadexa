<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('plan');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('status')->default('pending'); // pending | paid | failed
            $table->string('provider')->default('iyzico');
            $table->string('provider_ref')->nullable();
            $table->string('error')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
