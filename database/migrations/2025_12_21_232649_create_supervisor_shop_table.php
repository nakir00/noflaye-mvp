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
        Schema::create('supervisor_shop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['supervisor_id', 'shop_id']);
            $table->index('supervisor_id');
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_shop');
    }
};
