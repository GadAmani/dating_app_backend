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
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('password');
            $table->text('description')->nullable()->after('gender');
            $table->string('profile_image')->nullable()->after('description');
            // Optional: if you want a role column
            // $table->string('role')->default('user')->after('profile_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'description', 'profile_image']);
            // Optional: if you added role
            // $table->dropColumn('role');
        });
    }
};
