<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('story_viewers', function (Blueprint $table): void {
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('story_id')
                ->nullable()
                ->constrained('stories')
                ->cascadeOnDelete();
            $table->unique(['user_id', 'story_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_viewers');
    }
};
