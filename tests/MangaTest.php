<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class MangaTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix, $prefixPublic, $badRequestData;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefixPublic = $this->version . '/manga';
        $this->prefix = $this->version . '/groups/mangas';
        // seed the database
        $this->artisan('db:seed');

        $this->badRequestData = [
            'synopsis' => 'assadasds ad sd',
            'status' => 'ongoing',
            'origin' => 'jp',
            'alternative_titles' => '["woah title", "title3"]',
            'genres' => '[{"id":2},{"id":1}]'
        ];
    }

    public function test_can_get_all_mangas(){
        $res = $this->json('GET', $this->prefixPublic . '/all');
        $data = $res->response->getData();
        $this->assertEquals(2, count($data->data), "Count does not match.");
    }

    public function test_can_get_one_manga()
    {
        $res = $this->json('GET', $this->prefixPublic . '/get/1');
        $data = $res->response->getData();
        $this->assertEquals(1, $data->data->id, "ID does not match.");
    }

    public function test_handle_get_one_manga_that_doesnt_exist()
    {
        $res = $this->json('GET', $this->prefixPublic . '/get/69');
        $code = $res->response->getStatusCode();
        $this->assertEquals(404, $code, "Status Code does not match.");
    }

    public function test_can_get_all_mangas_user_created()
    {
        $res = $this->json('GET', $this->prefix . '/all', [], $this->getGroupUserHeader());
        $data = $res->response->getData();
        $this->assertEquals(1, count($data->data), "Count does not match.");
    }

    public function test_can_get_all_mangas_as_admin()
    {
        $res = $this->json('GET', $this->prefix . '/all', [], $this->getAdminHeader());
        $data = $res->response->getData();
        $this->assertEquals(3, count($data->data), "Count does not match.");
    }

    public function test_can_get_mangas_for_edit_user_created()
    {
        $res = $this->json('GET', $this->prefix . '/3', [], $this->getGroupUserHeader());
        $data = $res->response->getData();
        $this->assertEquals(3, $data->data->id, "ID does not match.");
    }

    public function test_can_get_mangas_for_edit_as_admin()
    {
        $res = $this->json('GET', $this->prefix . '/3', [], $this->getAdminHeader());
        $data = $res->response->getData();
        $this->assertEquals(3, $data->data->id, "ID does not match.");
    }

    public function test_handle_get_mangas_for_edit_created_by_different_user()
    {
        $res = $this->json('GET', $this->prefix . '/1', [], $this->getGroupUserHeader());
        $code = $res->response->getStatusCode();
        $this->assertEquals(406, $code, "Status Code does not match.");
    }

    public function test_handle_get_mangas_for_edit_as_weak_user()
    {
        $res = $this->json('GET', $this->prefix . '/1', [], $this->getUserHeader());
        $code = $res->response->getStatusCode();
        $this->assertEquals(401, $code, "Status Code does not match.");
    }

    public function test_handle_get_mangas_for_edit_as_guest_user()
    {
        $res = $this->json('GET', $this->prefix . '/1');
        $code = $res->response->getStatusCode();
        $this->assertEquals(401, $code, "Status Code does not match.");
    }

    public function test_can_add_manga(){
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('POST', $this->prefix, $data,[], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ], $this->getGroupUserHeader());
        $data = $res->baseResponse->getData();
        
        $this->assertTrue($data->data->cover !== null, "Cover upload might've went wrong.");
        $this->assertResponseStatus(201);
    }

    public function test_can_update_manga()
    {
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('PUT', $this->prefix . '/3', $data, [], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ], $this->getGroupUserHeader());
        $data = $res->baseResponse->getData();

        $this->assertTrue($data->data->cover !== null, "Cover upload might've went wrong.");
        $this->assertResponseStatus(200);
    }

    public function test_can_delete_manga()
    {
        $res = $this->json('DELETE', $this->prefix . '/3', [], $this->getGroupUserHeader());
        $this->assertResponseStatus(200);
    }

    public function test_handle_add_manga_with_bad_data(){
        $data = $this->badRequestData;

        $res = $this->call('POST', $this->prefix, $data, [], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ], $this->getGroupUserHeader());
        $data = $res->baseResponse->getData();
        $this->assertResponseStatus(422);
    }
    public function test_handle_add_manga_without_cover()
    {
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('POST', $this->prefix, $data, [], [], $this->getGroupUserHeader());
        $data = $res->baseResponse->getData();
        $this->assertResponseStatus(422);
    }

    public function test_handle_add_manga_with_weak_user_account()
    {
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('POST', $this->prefix, $data, [], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ], $this->getUserHeader());

        $this->assertResponseStatus(401);
    }
    public function test_handle_add_manga_with_guest_account()
    {
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('POST', $this->prefix, $data, [], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ]);

        $this->assertResponseStatus(401);
    }

    public function test_handle_update_manga_that_user_didnt_create(){
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('PUT', $this->prefix . '/1', $data, [], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ], $this->getGroupUserHeader());
        
        $this->assertResponseStatus(406);
    }

    public function test_handle_update_manga_that_doesnt_exist()
    {
        $data = $this->badRequestData;
        $data['title'] = "Test Title";

        $res = $this->call('PUT', $this->prefix . '/69', $data, [], [
            'cover' => UploadedFile::fake()->image('file.jpg')
        ], $this->getGroupUserHeader());

        $this->assertResponseStatus(404);
    }


    public function test_handle_delete_manga_that_user_didnt_create()
    {
        $res = $this->json('DELETE', $this->prefix . '/1', [], $this->getGroupUserHeader());
        $this->assertResponseStatus(406);
    }

    public function test_handle_delete_manga_that_doesnt_exist()
    {
        $res = $this->json('DELETE', $this->prefix . '/69', [], $this->getGroupUserHeader());
        $this->assertResponseStatus(404);
    }
}
