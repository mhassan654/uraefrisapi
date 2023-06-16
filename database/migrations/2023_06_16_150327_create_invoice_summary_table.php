<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceSummaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_summary', function (Blueprint $table) {
            $table->id();
            $table->string('businessName');
            $table->string('buyerBusinessName');
            $table->string('buyerLegalName');
            $table->unsignedBigInteger('buyerTin');
            $table->string('currency');
            $table->unsignedBigInteger('dataSource');
            $table->string('dateFormat');
            $table->decimal('grossAmount', 15, 2);
            $table->integer('invoiceIndustryCode');
            $table->integer('invoiceKind');
            $table->integer('invoiceNo')->unique();
            $table->integer('invoiceType');
            $table->integer('isInvalid');
            $table->integer('isRefund');
            $table->string('issuedDate');
            $table->string('issuedDateStr');
            $table->string('legalName');
            $table->timestamp('nowTime')->nullable();
            $table->integer('pageIndex');
            $table->integer('pageNo');
            $table->integer('pageSize');
            $table->decimal('taxAmount', 15, 2);
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
        Schema::dropIfExists('invoice_summary');
    }
}
