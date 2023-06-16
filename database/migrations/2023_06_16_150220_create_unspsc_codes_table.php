<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnspscCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unspsc_codes', function (Blueprint $table) {
            $table->id();
            $table->string('segment');
            $table->string('segmentName');
            $table->string('family');
            $table->string('familyName');
            $table->string('class');
            $table->string('className');
            $table->string('commodity');
            $table->string('commodityName');
            $table->string('exciseDutyProductType');
            $table->string('vatRate');
            $table->string('serviceMark');
            $table->string('zeroRate');
            $table->timestamp('zeroRateBegingTime')->nullable();
            $table->string('exempt');
            $table->timestamp('exemptBegingTime')->nullable();
            $table->string('exclusion');
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
        Schema::dropIfExists('unspsc_codes');
    }
}
