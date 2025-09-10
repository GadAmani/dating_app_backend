<?php

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
    Schema::create('likes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // user who liked
        $table->foreignId('liked_user_id')->constrained('users')->onDelete('cascade'); // user who got liked
        $table->timestamps();
        $table->unique(['user_id', 'liked_user_id']); // prevent duplicate likes
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('likes');
}

};
