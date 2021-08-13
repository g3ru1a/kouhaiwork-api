<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mangas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('synopsis');
            $table->text('alternative_titles')->nullable();
            $table->tinyText('status');
            $table->text('cover_url');
            $table->string('origin')->default('jp');
            $table->timestamps();
        });

        Schema::create('manga_manga_genre', function (Blueprint $table) {
            $table->id();
            $table->integer('manga_id');
            $table->integer('manga_genre_id');
        });

        Schema::create('manga_manga_theme', function (Blueprint $table) {
            $table->id();
            $table->integer('manga_id');
            $table->integer('manga_theme_id');
        });

        Schema::create('manga_manga_demographic', function (Blueprint $table) {
            $table->id();
            $table->integer('manga_id');
            $table->integer('manga_demographic_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mangas');
        Schema::dropIfExists('manga_manga_genres');
        Schema::dropIfExists('manga_manga_theme');
        Schema::dropIfExists('manga_manga_demographic');
    }
}
