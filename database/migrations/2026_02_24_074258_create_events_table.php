<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partnership_id');
            $table->uuid('created_by');
            $table->string('title', 200);
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('venue', 200)->nullable();
            $table->text('notes')->nullable();
            $table->string('emoji', 10)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 50)->nullable();
            $table->timestamps();

            $table->foreign('partnership_id')->references('id')->on('partnerships');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};