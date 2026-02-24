<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checklist_id');
            $table->uuid('assigned_to')->nullable();
            $table->string('label', 300);
            $table->boolean('is_completed')->default(false);
            $table->uuid('completed_by')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('checklist_id')
                  ->references('id')
                  ->on('checklists')
                  ->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};