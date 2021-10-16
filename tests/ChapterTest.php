<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ChapterTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix, $prefixPublic, $badRequestData;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefixPublic = $this->version . '/chapters';
        $this->prefix = $this->version . '/groups/chapters';
        // seed the database
        $this->artisan('db:seed');

        $this->badRequestData = [
            'volume' => '67',
            'name' => 'chapter name',
            'manga_id' => '1',
            'groups' => '[1]'
        ];
    }

    public function test_can_get_chapter(){
        $res = $this->json('GET', $this->prefixPublic.'/get/1');
        $this->assertEquals(200, $res->response->getStatusCode(), "Response status not 200");
    }

    public function test_can_get_latest_chapter()
    {
        $res = $this->json('GET', $this->prefixPublic . '/latest');
        $this->assertEquals(200, $res->response->getStatusCode(), "Response status not 200");
    }

    public function test_handle_get_chapter_that_doesnt_exist()
    {
        $res = $this->json('GET', $this->prefixPublic . '/get/69');
        $this->assertEquals(404, $res->response->getStatusCode(), "Response status not 404");
    }

    public function test_can_get_chapter_for_edit()
    {
        $res = $this->json('GET', $this->prefix . '/1', [], $this->getAdminHeader());
        $this->assertEquals(200, $res->response->getStatusCode(), "Response status not 200");
    }

    public function test_handle_get_chapter_for_edit_as_guest()
    {
        $res = $this->json('GET', $this->prefix . '/1');
        $this->assertEquals(401, $res->response->getStatusCode(), "Response status not 401");
    }

    public function test_handle_get_chapter_for_edit_as_wrong_group_owner()
    {
        $res = $this->json('GET', $this->prefix . '/1', [], $this->getGroupUserHeader());
        $this->assertEquals(406, $res->response->getStatusCode(), "Response status not 406");
    }
    
    public function test_handle_get_chapter_for_edit_that_doesnt_exist()
    {
        $res = $this->json('GET', $this->prefix . '/69', [], $this->getAdminHeader());
        $this->assertEquals(404, $res->response->getStatusCode(), "Response status not 404");
    }

    public function test_can_add_chapter(){
        $data = $this->badRequestData;
        $data['number'] = "23";
        $res = $this->json('POST', $this->prefix, $data, $this->getAdminHeader());
        $this->assertEquals(201, $res->response->getStatusCode(), "Response status not 201");
    }

    public function test_can_update_chapter()
    {
        $data = $this->badRequestData;
        $data['number'] = "11";
        $res = $this->json('PUT', $this->prefix.'/1', $data, $this->getAdminHeader());
        $this->assertEquals(200, $res->response->getStatusCode(), "Response status not 200");
        $this->assertEquals(11, $res->response->getData()->data->number, "Number didn't update");
    }

    public function test_handle_add_or_update_chapter_as_guest_user()
    {
        $data = $this->badRequestData;
        $data['number'] = "23";
        $res = $this->json('POST', $this->prefix, $data);
        $this->assertEquals(401, $res->response->getStatusCode(), "Response status not 401");
    }

    public function test_handle_add_or_update_chapter_as_weak_user()
    {
        $data = $this->badRequestData;
        $data['number'] = "23";
        $res = $this->json('POST', $this->prefix, $data, $this->getUserHeader());
        $this->assertEquals(401, $res->response->getStatusCode(), "Response status not 401");
    }

    public function test_handle_add_or_update_chapter_that_doesnt_exist()
    {
        $data = $this->badRequestData;
        $data['number'] = "23";
        $res = $this->json('PUT', $this->prefix . '/69', $data, $this->getAdminHeader());
        $this->assertEquals(404, $res->response->getStatusCode(), "Response status not 404");
    }

    public function test_can_delete_chapter()
    {
        $res = $this->json('DELETE', $this->prefix . '/1', $this->getAdminHeader());
        $this->assertEquals(200, $res->response->getStatusCode(), "Response status not 200");
    }

    public function test_handle_delete_chapter_as_guest_user()
    {
        $res = $this->json('DELETE', $this-> prefix . '/1');
        $this->assertEquals(401, $res->response->getStatusCode(), "Response status not 401");
    }

    public function test_handle_delete_chapter_as_weak_user()
    {
        $res = $this->json('DELETE', $this-> prefix . '/1', [], $this->getUserHeader());
        $this->assertEquals(401, $res->response->getStatusCode(), "Response status not 401");
    }

    public function test_handle_delete_chapter_that_doesnt_exist()
    {
        $res = $this->json('DELETE', $this->prefix . '/69', [], $this->getAdminHeader());
        $this->assertEquals(404, $res->response->getStatusCode(), "Response status not 404");
    }
}
