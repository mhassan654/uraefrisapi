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
            $table->unsignedBigInteger('antifake_code');
            $table->string('currency');
            $table->unsignedBigInteger('data_source');
            $table->unsignedBigInteger('device_no');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('invoice_industry_code');
            $table->unsignedBigInteger('invoice_kind');
            $table->unsignedBigInteger('invoice_no');
            $table->unsignedBigInteger('invoice_type');
            $table->unsignedBigInteger('is_batch');
            $table->unsignedBigInteger('is_invalid');
            $table->unsignedBigInteger('is_refund');
            $table->date('issued_date');
            $table->date('issued_date_pdf');
            $table->string('operator');
            $table->string('pay_way');

            // buyerDetails
            $table->string('buyer_address');
            $table->string('buyer_legal_name');
            $table->string('buyer_sector');
            $table->unsignedBigInteger('buyer_type');

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
