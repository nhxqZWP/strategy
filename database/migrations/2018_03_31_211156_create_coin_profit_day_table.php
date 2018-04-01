<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinProfitDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('everyday_profit', function (Blueprint $table) {
              $table->unsignedInteger("uid");
              $table->string("coin_name", 10);
              $table->unsignedBigInteger("coin_avail");
              $table->unsignedBigInteger("coin_onorder");
              $table->unsignedBigInteger("usdt_avail");
              $table->unsignedBigInteger("usdt_onorder");
              $table->unsignedInteger("trade_no");
              $table->unsignedTinyInteger("type")->default(1);
              $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
//              $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

              $table->index('uid');
              $table->unique(['uid', 'trade_no']);
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
