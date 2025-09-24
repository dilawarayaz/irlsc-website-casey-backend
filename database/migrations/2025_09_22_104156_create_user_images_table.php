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
        Schema::create('user_images', function (Blueprint $table) {
          
            $table->id();
            $table->integer('user_id');
            $table->string('image_path'); // Path or URL to the image
            $table->boolean('is_primary')->default(false); // Mark if this is the main profile picture
            $table->integer('order')->default(0); // For sorting images
            $table->timestamps();
        });
   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_images');
    }
};
