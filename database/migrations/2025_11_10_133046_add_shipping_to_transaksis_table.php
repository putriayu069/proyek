<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingToTransaksisTable extends Migration
{
    public function up()
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->text('alamat_pengiriman')->nullable()->after('nama_barang');
            $table->integer('ongkir')->default(0)->after('total_harga');
            $table->string('kurir')->nullable()->after('ongkir');
            $table->string('service')->nullable()->after('kurir'); // layanan JNE REG / YES dll
        });
    }

    public function down()
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['alamat_pengiriman', 'ongkir', 'kurir', 'service']);
        });
    }
}
