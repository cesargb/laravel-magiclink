<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToMagicLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('magic_links', function (Blueprint $table) {
            $table->index('available_at');
            $table->index('max_visits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('magic_links', function (Blueprint $table) {
            $table->dropIndex('magic_links_available_at_index');
            $table->dropIndex('magic_links_max_visits_index');
        });
    }
}
