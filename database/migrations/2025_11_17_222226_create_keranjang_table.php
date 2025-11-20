<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Index untuk performa query
            $table->index(['user_id', 'barang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};