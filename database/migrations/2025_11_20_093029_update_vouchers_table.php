<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // Kode unik voucher
            $table->string('nama'); // Nama voucher
            $table->text('deskripsi')->nullable(); // Deskripsi voucher
            $table->enum('tipe_diskon', ['persen', 'nominal'])->default('persen'); // Jenis diskon
            $table->decimal('nilai_diskon', 8, 2); // Nilai diskon (misal 10.00%)
            $table->date('tanggal_mulai')->nullable(); // Periode mulai
            $table->date('tanggal_berakhir')->nullable(); // Periode akhir
            $table->integer('kuota')->default(0); // Jumlah maksimum penggunaan
            $table->boolean('aktif')->default(true); // Status voucher aktif/tidak
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};