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
        Schema::create('model_formats', function (Blueprint $table) {
            $table->id();
            $table->string('format');
            $table->text('model_file');
            // Explicitly defining the foreign key column type
            $table->unsignedBigInteger('model3d_id'); // Ensure this matches the primary key type of `model3ds`
            $table->foreign('model3d_id')->references('id')->on('model3ds')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_formats');
    }
};
