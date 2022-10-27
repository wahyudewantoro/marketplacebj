<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewPbbMinimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* Schema::create('view_pbb_minimal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        }); */

        DB::statement("CREATE VIEW PBB_MINIMAL AS
        SELECT * FROM PBB_MINIMAL@TO17");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW PBB_MINIMAL");
    }
}
