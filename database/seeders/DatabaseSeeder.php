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
        $u1 = new User([
            'name' => 'test-acc', 
            'email' => 'test@email.com', 
            'rank' => 3, 
            'password' => app('hash')->make('123456'),
            'verified' => 1,
            'verify_token' => sha1(time()),
        ]);
        $u1->save();
        $u = new User([
            'name' => 'test-acc-pleb',
            'email' => 'test-weak@email.com',
            'rank' => 1,
            'password' => app('hash')->make('123456'),
            'verified' => 1,
            'verify_token' => '',
        ]);
        $u->save();
        Artist::factory()->count(3)->create();
        Author::factory()->count(3)->create();
        MangaDemographic::factory()->count(3)->create();
        MangaTheme::factory()->count(3)->create();
        MangaGenre::factory()->count(3)->create();
        Group::factory()->count(5)->create();
        $g = Group::find(1);
        $g->members()->save($u);
        $group = Group::create([
            'name' => 'GroupSeed',
            'owner_id' => 2
        ]);
        $group->members()->save($u1);
        $group = Group::create([
            'name' => 'GroupSeed',
            'owner_id' => 2
        ]);
        Post::factory()->count(3)->create();
        Manga::factory()
            ->count(5)->create();
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
