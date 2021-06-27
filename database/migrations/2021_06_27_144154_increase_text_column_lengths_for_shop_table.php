<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncreaseTextColumnLengthsForShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopware_6_shop', static function (Blueprint $table): void {
            $table->dropColumn([
                'shop_url',
                'shop_secret',
                'api_key',
                'secret_key',
            ]);
        });
        Schema::table('shopware_6_shop', static function (Blueprint $table): void {
            $table->mediumText('shop_url')->after('shop_id');
            $table->mediumText('shop_secret')->after('shop_url');
            $table->mediumText('api_key')->nullable()->after('shop_secret');
            $table->mediumText('secret_key')->nullable()->after('api_key');
        });
        DB::table('shopware_6_shop')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopware_6_shop', static function (Blueprint $table): void {
            $table->dropColumn([
                'shop_url',
                'shop_secret',
                'api_key',
                'secret_key',
            ]);
        });

        Schema::table('shopware_6_shop', static function (Blueprint $table): void {
            $table->string('shop_url')->after('shop_id');
            $table->string('shop_secret')->after('shop_url');
            $table->string('api_key')->nullable()->after('shop_secret');
            $table->string('secret_key')->nullable()->after('api_key');
        });
        DB::table('shopware_6_shop')->delete();
    }
}
