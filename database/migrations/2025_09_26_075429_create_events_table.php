<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('location');
            $table->string('address')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('images')->nullable();
            $table->string('category');
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('max_attendees');
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};