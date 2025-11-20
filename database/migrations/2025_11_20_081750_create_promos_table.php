<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('promos', function (Blueprint $table) {
        $table->id();
        $table->string('kode')->unique();
        $table->integer('percent')->nullable();   // diskon persen
        $table->integer('amount')->nullable();    // diskon nominal
        $table->date('expired_at')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('promos');
}
};
