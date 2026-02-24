<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('goal_id');
            $table->uuid('contributed_by');
            $table->decimal('amount', 12, 2);
            $table->string('note', 300)->nullable();
            $table->date('contributed_at')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();

            $table->foreign('goal_id')
                  ->references('id')
                  ->on('savings_goals')
                  ->onDelete('cascade');
            $table->foreign('contributed_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
    }
};