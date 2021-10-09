<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TestSeeder::class);
        // return;
        // User::factory()->count(50)->create();
        // Manga::factory()
        //     ->count(20)
        //     ->has(MangaGenre::factory()->count(3), 'genres')
        //     ->has(MangaTheme::factory()->count(3), 'themes')
        //     ->has(MangaDemographic::factory()->count(3), 'demographics')
        //     ->has(Group::factory()->count(1), 'groups')
        //     ->has(Author::factory()->count(1), 'authors')
        //     ->has(Artist::factory()->count(1), 'artists')
        //     ->has(Media::factory(), 'cover')
        //     ->create();
        // Post::factory()->count(10)->create();
    }
}
