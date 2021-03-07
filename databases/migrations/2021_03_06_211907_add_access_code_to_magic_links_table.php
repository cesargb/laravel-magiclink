<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessCodeToMagicLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('magic_links', function (Blueprint $table) {
            $table->string('access_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('magic_links', 'access_code')) {
            Schema::table('magic_links', function (Blueprint $table) {
                $table->dropColumn('access_code');
            });
        }
    }
}
