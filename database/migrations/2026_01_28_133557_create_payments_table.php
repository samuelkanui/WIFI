<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->string('phone')->nullable();
            $table->string('gateway')->default('mpesa');
            $table->string('transaction_id')->unique();
            $table->string('mpesa_receipt')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->foreignId('tariff_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
