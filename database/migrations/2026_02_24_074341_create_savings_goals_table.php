<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partnership_id');
            $table->uuid('created_by');
            $table->string('title', 200);
            $table->string('emoji', 10)->nullable();
            $table->decimal('target_amount', 12, 2);
            $table->char('currency', 3)->default('PHP');
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'completed', 'archived'])->default('active');
            $table->timestamps();
            $table->string('notes', 200);
            $table->foreign('partnership_id')->references('id')->on('partnerships');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};