<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->string('lang', 20);
            $table->timestamps();

            $table->unique(['faq_id', 'lang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_translations');
    }
};
