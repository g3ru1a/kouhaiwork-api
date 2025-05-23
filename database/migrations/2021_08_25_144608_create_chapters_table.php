<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaptersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->integer('volume')->nullable();
            $table->float('number');
            $table->string('name')->nullable();
            $table->integer('manga_id');
            $table->integer('group_id')->nullable();
            $table->boolean('uploaded')->default(false)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('chapter_group', function (Blueprint $table) {
            $table->id();
            $table->integer('chapter_id');
            $table->integer('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('chapter_group');
    }
}
