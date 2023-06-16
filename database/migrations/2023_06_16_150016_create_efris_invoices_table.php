<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEfrisInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('efris_invoices', function (Blueprint $table) {
            $table->id();
            $table->json('basicInformation');

            // buyerDetails
            $table->json('buyerDetails');

            // extend
            $table->json('extend');
            $table->json('goodsDetails');
            $table->json('payWay');
            $table->json('summary');
            $table->json('sellerDetails');
            $table->json('taxDetails');
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
        Schema::dropIfExists('efris_invoices');
    }
}
