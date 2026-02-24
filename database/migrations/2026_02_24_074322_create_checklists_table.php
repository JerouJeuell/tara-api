<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partnership_id');
            $table->uuid('event_id')->nullable();
            $table->uuid('created_by');
            $table->string('title', 200);
            $table->timestamps();

            $table->foreign('partnership_id')->references('id')->on('partnerships');
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};