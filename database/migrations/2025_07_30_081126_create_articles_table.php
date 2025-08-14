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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('image')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            //$table->string('slug')->unique();
            //$table->enum('status', ['draft', 'review', 'published'])->default('draft');
            //$table->foreignId('category_id')->nullable()->constrained('categories');
            //$table->foreignId('editor_id')->nullable()->constrained('users');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
