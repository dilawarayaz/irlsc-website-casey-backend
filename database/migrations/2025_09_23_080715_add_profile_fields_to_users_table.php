<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable();
            $table->integer('age')->nullable();
            $table->string('location')->nullable();
            $table->string('occupation')->nullable();
            $table->string('education')->nullable();
            $table->json('interests')->nullable();
            $table->string('looking_for')->nullable();
            $table->string('relationship_goals')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'age',
                'location',
                'occupation',
                'education',
                'interests',
                'looking_for',
                'relationship_goals',
            ]);
        });
    }
};
