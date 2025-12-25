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
        Schema::create('shop_kitchen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitchen_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shop_id', 'kitchen_id']);
            $table->index('shop_id');
            $table->index('kitchen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_kitchen');
    }
};
