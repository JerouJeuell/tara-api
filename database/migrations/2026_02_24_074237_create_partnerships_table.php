<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnerships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_a_id');
            $table->uuid('user_b_id');
            $table->enum('status', ['pending', 'active', 'dissolved'])->default('pending');
            $table->date('anniversary_date')->nullable();
            $table->uuid('initiated_by');
            $table->timestampTz('connected_at')->nullable();
            $table->timestamps();

            $table->foreign('user_a_id')->references('id')->on('users');
            $table->foreign('user_b_id')->references('id')->on('users');
            $table->foreign('initiated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnerships');
    }
};