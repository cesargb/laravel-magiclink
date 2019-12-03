<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMagicLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('magiclink.magiclink_table', 'magic_links'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('token', 100);
            $table->string('redirect_url')->nullable();
            $table->timestamps();
            $table->timestamp('available_at')->nullable();
            $table->foreign('user_id')->references(config('magiclink.user_primarykey', 'id'))->on(config('magiclink.user_table', 'users'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('magiclink.magiclink_table', 'magic_links'));
    }
}
