<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Author;
use App\Models\Group;
use App\Models\Manga;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use App\Models\Media;
use App\Models\User;
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
        $u = new User(['name'=>'g3ru1a', 'email'=>'test@email.com', 'rank'=>3, 'password'=>app('hash')->make('123456')]);
        $u->save();
        Group::factory()->count(4)->create();
        Manga::factory()
            ->count(5)
            ->has(MangaGenre::factory()->count(3), 'genres')
            ->has(MangaTheme::factory()->count(3), 'themes')
            ->has(MangaDemographic::factory()->count(3), 'demographics')
            ->has(Group::factory()->count(1), 'groups')
            ->has(Author::factory()->count(1), 'authors')
            ->has(Artist::factory()->count(1), 'artists')
            ->has(Media::factory(), 'cover')
            ->create();
    }
}
