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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('image');
            $table->string('price');
            $table->string('price_afterdiscount')->nullable();; #nullable(kolomnya boleh kosong)
            $table->string('percent')->nullable();;
            $table->string('is_promo')->nullable();;
            $table->string('categories_id')->constrained('categories')->cascadeOnDelete(); #Refer ke tabel categories
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
