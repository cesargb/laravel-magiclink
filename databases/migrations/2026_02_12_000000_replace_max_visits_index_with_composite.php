<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceMaxVisitsIndexWithComposite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('magic_links', function (Blueprint $table) {
            $table->dropIndex('magic_links_max_visits_index');
            $table->index(['max_visits', 'num_visits']);
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
            $table->dropIndex(['max_visits', 'num_visits']);
            $table->index('max_visits');
        });
    }
}
