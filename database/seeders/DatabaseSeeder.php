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
use App\Models\Post;
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
        $u = new User([
            'name' => 'test-acc', 
            'email' => 'test@email.com', 
            'rank' => 3, 
            'password' => app('hash')->make('123456'),
            'verified' => 1,
            'verify_token' => sha1(time()),
        ]);
        $u->save();
        // return;
        User::factory()->count(50)->create();
        Manga::factory()
            ->count(20)
            ->has(MangaGenre::factory()->count(3), 'genres')
            ->has(MangaTheme::factory()->count(3), 'themes')
            ->has(MangaDemographic::factory()->count(3), 'demographics')
            ->has(Group::factory()->count(1), 'groups')
            ->has(Author::factory()->count(1), 'authors')
            ->has(Artist::factory()->count(1), 'artists')
            ->has(Media::factory(), 'cover')
            ->create();
        // Post::factory()->count(10)->create();
    }
}
