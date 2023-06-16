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
            $table->unsignedBigInteger('antifakeCode');
            $table->string('currency');
            $table->unsignedBigInteger('dataSource');
            $table->unsignedBigInteger('deviceNo');
            $table->unsignedBigInteger('invoiceId');
            $table->unsignedBigInteger('invoiceIndustryCode');
            $table->unsignedBigInteger('invoiceKind');
            $table->unsignedBigInteger('invoiceNo');
            $table->unsignedBigInteger('invoiceType');
            $table->unsignedBigInteger('isBatch');
            $table->unsignedBigInteger('isInvalid');
            $table->unsignedBigInteger('isRefund');
            $table->timestamp('issuedDate');
            $table->timestamp('issuedDatePdf');
            $table->string('operator');
            $table->string('payWay');
            $table->string('buyerAddress');
            $table->string('buyerLegalName');
            $table->string('buyerSector');
            $table->unsignedBigInteger('buyerType');
            $table->string('reason');
            $table->unsignedBigInteger('reasonCode');
            $table->unsignedBigInteger('deemedFlag');
            $table->unsignedBigInteger('discountFlag');
            $table->string('exciseCurrency');
            $table->unsignedBigInteger('exciseFlag');
            $table->unsignedBigInteger('exciseTax');
            $table->unsignedBigInteger('goodsCategoryId');
            $table->string('goodsCategoryName');
            $table->string('item');
            $table->string('itemCode');
            $table->unsignedBigInteger('orderNumber');
            $table->decimal('qty', 12, 2);
            $table->decimal('tax', 12, 2);
            $table->decimal('taxRate', 12, 2);
            $table->decimal('total', 12, 2);
            $table->unsignedBigInteger('unitOfMeasure');
            $table->decimal('unitPrice', 12, 2);
            $table->string('paymentMode');
            $table->decimal('paymentAmount', 12, 2);
            $table->unsignedBigInteger('paymentOrderNumber');
            $table->string('address');
            $table->string('businessName');
            $table->string('emailAddress');
            $table->string('legalName');
            $table->string('ninBrn');
            $table->string('placeOfBusiness');
            $table->string('referenceNo');
            $table->unsignedBigInteger('tin');
            $table->decimal('grossAmount', 12, 2);
            $table->unsignedBigInteger('itemCount');
            $table->unsignedBigInteger('modeCode');
            $table->decimal('netAmount', 12, 2);
            $table->string('qrCode');
            $table->decimal('taxAmount', 12, 2);
            $table->string('taxCategory');
            $table->decimal('netAmount', 12, 2);
            $table->decimal('taxRate', 12, 2);
            $table->decimal('taxAmount', 12, 2);
            $table->decimal('grossAmount', 12, 2);
            $table->unsignedBigInteger('exciseUnit');
            $table->string('exciseCurrency');
            $table->string('taxRateName');
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
