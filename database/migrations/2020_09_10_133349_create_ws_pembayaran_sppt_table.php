<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWsPembayaranSpptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('nop', 18);
            $table->string('merchant');
            $table->dateTime('datetime', 0);
            $table->bigInteger('totalbayar');
            $table->string('kodepengesahan');
            $table->string('kodekp');
            $table->timestamps();
        });

        Schema::create('ws_pembayaran_tahun', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ws_pembayaran_id');
            $table->char('nop', 18);
            $table->integer('tahun_pajak');
            $table->string('kodepengesahan');
            $table->string('kodekp');
            $table->bigInteger('pokok');
            $table->bigInteger('denda');
            $table->bigInteger('total');
            $table->dateTime('datetime', 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('ws_pembayaran_global');
    }
}
