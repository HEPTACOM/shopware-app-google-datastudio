<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHcCloudShopInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hc_cloud_shop_information', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->string('shop-id');
            $table->string('shop-url');
            $table->string('apiKey');
            $table->string('secretKey');
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
        Schema::dropIfExists('hc_cloud_shop_information');
    }
}
