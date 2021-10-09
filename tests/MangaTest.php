<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class MangaTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix, $good_values;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefix = $this->version . '/groups/mangas';
        // seed the database
        $this->artisan('db:seed');

        $this->good_values = [
            'title' => 'TestTitle',
            'synopsis' => 'this is quite the synopsis',
            'alternative_titles' => json_encode(['TIIIIIITLE','aaaaaaa title']),
            'status' => 'ongoing',
            'origin' => 'jp',
            'genres' => json_encode([
                ['id' => 1],
                ['id' => 2],
            ])
        ];
    }

    private function getAdminMPHeader(){
        $h = $this->getAdminHeader();
        $h['Content-Type'] = 'multipart/form-data';
        return $h;
    }

    private function getDataWithFakeCover(){
        Storage::fake('covers');
        $file = UploadedFile::fake()->image('cover1.jpg');
        $arr = $this->good_values;
        $arr['cover'] = $file;
        return $arr;
    }
}
