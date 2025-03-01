<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('magic_links', function (Blueprint $table) {
            $table->string('base_url')->nullable()->after('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('magic_links', 'base_url')) {
            Schema::table('magic_links', function (Blueprint $table) {
                $table->dropColumn('base_url');
            });
        }
    }
};
