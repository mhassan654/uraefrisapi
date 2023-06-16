<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEfrisProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('efris_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commodityCategoryCode');
            $table->string('commodityCategoryName');
            $table->unsignedBigInteger('createDate');
            $table->unsignedBigInteger('currency');
            $table->string('dateFormat');
            $table->unsignedBigInteger('exclusion');
            $table->string('goodsCode')->unique();
            $table->string('goodsName');
            $table->unsignedBigInteger('haveExciseTax');
            $table->unsignedBigInteger('havePieceUnit');
            $table->string('isExempt');
            $table->string('isZeroRate');
            $table->string('measureUnit');
            $table->timestamp('nowTime')->nullable();
            $table->unsignedBigInteger('pageIndex');
            $table->unsignedBigInteger('pageNo');
            $table->unsignedBigInteger('pageSize');
            $table->string('remarks');
            $table->unsignedBigInteger('source');
            $table->unsignedBigInteger('statusCode');
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('stockPrewarning');
            $table->unsignedBigInteger('taxRate');
            $table->string('timeFormat');
            $table->unsignedBigInteger('unitPrice');
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
        Schema::dropIfExists('efris_products');
    }
}
