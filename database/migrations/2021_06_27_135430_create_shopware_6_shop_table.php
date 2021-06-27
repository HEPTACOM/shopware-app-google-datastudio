<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopware6ShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopware_6_shop', static function (Blueprint $table): void {
            $table->uuid('id')->unique();
            $table->string('shop_id');
            $table->string('shop_url');
            $table->string('shop_secret');
            $table->string('api_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('hc_cloud_shop_information');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('hc_cloud_shop_information', static function (Blueprint $table): void {
            $table->uuid('id')->unique();
            $table->string('shop-id');
            $table->string('shop-url');
            $table->string('apiKey');
            $table->string('secretKey');
            $table->timestamps();
        });
        Schema::dropIfExists('shopware_6_shop');
    }
}
