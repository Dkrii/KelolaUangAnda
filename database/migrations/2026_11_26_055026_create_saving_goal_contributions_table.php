<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_goal_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_goal_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('contribution_date');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['saving_goal_id', 'contribution_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_goal_contributions');
    }
};
