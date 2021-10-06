<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefix = '/v' . env('APP_VERSION', 'nan') . '/groups/manga/genres';
        // seed the database
        $this->artisan('db:seed');
    }

    public function test_can_fetch_genres()
    {
        $res = $this->json('GET', $this->version . '/manga/genres');
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_can_add_genre(){
        $res = $this->json('POST', $this->prefix, [
            'name' => 'testo namewa',
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_handle_add_genre_request_from_guest_user()
    {
        $res = $this->json('POST', $this->prefix, [
            'name' => 'testo namewa',
        ]);
        $res = $res->response->original;
        $this->assertTrue($res === 'Unauthorized.');
    }
    public function test_handle_add_genre_request_from_normal_user()
    {
        $res = $this->json('POST', $this->prefix, [
            'name' => 'testo namewa',
        ], $this->getUserHeader());
        $res = $res->response->original;
        $this->assertTrue($res === 'Rank Unauthorized.');
    }

    public function test_can_update_genres()
    {
        $res = $this->json('PUT', $this->prefix . '/3', [
            'name' => 'testo namewa',
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_handle_update_genre_request_with_wrong_id()
    {
        $res = $this->json('PUT', $this->prefix . '/69', [
            'name' => 'testo namewa',
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->error) && $res->error->status === '404');
    }

    public function test_can_delete_genres()
    {
        $res = $this->json('DELETE', $this->prefix . '/3', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_handle_delete_genre_request_with_wrong_id()
    {
        $res = $this->json('DELETE', $this->prefix . '/69', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->error) && $res->error->status === '404');
    }
}
