<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('match_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // jis user ne request bheji
            $table->string('relationship_type')->nullable(); // serious/casual
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('handled_by')->nullable(); // admin id
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_requests');
    }
};
