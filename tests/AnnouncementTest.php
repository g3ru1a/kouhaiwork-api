<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AnnouncementTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefix = $this->version . '/admins/announcements';
        // seed the database
        $this->artisan('db:seed');
    }

    public function test_can_get_announcements(){
        $res = $this->json('GET', $this->version.'/announcements');
        $data = $res->response->getData();
        $count = count($data->data);
        $this->assertEquals(3, $count, 'Announcement count does not match what was expected. Expected: 3; Found: '.$count);
        $this->assertTrue($res->response->getStatusCode() === 200, "Response status was not 200.");
    }

    public function test_can_add_announcement(){
        $res = $this->json('POST', $this->prefix, [
            'title' => 'some title',
            'body' => '<p>Body</p>',
        ], $this->getAdminHeader());
        $data = $res->response->getData();
        $this->assertEquals(4, $data->data->id, 'Announcment has an ID that was not expected.');
    }

    public function test_can_update_announcement()
    {
        $res = $this->json('PUT', $this->prefix.'/3', [
            'title' => 'some title',
            'body' => '<p>Body</p>',
        ], $this->getAdminHeader());
        $data = $res->response->getData();
        $this->assertEquals(3, $data->data->id, 'Announcment has an ID that was not expected.');
        $this->assertEquals('some title', $data->data->title, 'Announcment Title was not updated.');
    }
    public function test_can_delete_announcement()
    {
        $res = $this->json('DELETE', $this->prefix . '/1', [], $this->getAdminHeader());
        $code = $res->response->getStatusCode();
        $this->assertEquals(200, $code, "Status code was different from 200");
    }

    public function test_handle_add_or_update_with_guest_account(){
        $res = $this->json('POST', $this->prefix, [
            'title' => 'some title',
            'body' => '<p>Body</p>',
        ]);
        $code = $res->response->getStatusCode();
        $content = $res->response->getContent();
        $this->assertEquals(401, $code, "Status code was different from 401");
        $this->assertEquals('Unauthorized.', $content, "Didn't reject because user was not authorized.");
    }

    public function test_handle_add_or_update_with_weak_account()
    {
        $res = $this->json('POST', $this->prefix, [
            'title' => 'some title',
            'body' => '<p>Body</p>',
        ], $this->getUserHeader());
        $code = $res->response->getStatusCode();
        $content = $res->response->getContent();
        $this->assertEquals(401, $code, "Status code was different from 401");
        $this->assertEquals('Rank Unauthorized.', $content, "Didn't reject because of wrong Rank.");
    }

    public function test_handle_add_or_update_announcement_with_bad_request()
    {
        $res = $this->json('PUT', $this->prefix . '/3', [
            'body' => '<p>Body</p>',
        ], $this->getAdminHeader());
        $code = $res->response->getStatusCode();
        $this->assertEquals(422, $code, 'Status code was not expected. Expected 422; Found: '.$code);
    }

    public function test_handle_update_announcement_that_doesnt_exist()
    {
        $res = $this->json('PUT', $this->prefix . '/69', [
            'title' => 'some title',
            'body' => '<p>Body</p>',
        ], $this->getAdminHeader());
        $code = $res->response->getStatusCode();
        $this->assertEquals(404, $code, 'Status code was not expected. Expected 404; Found: ' . $code);
    }

    public function test_handle_delete_announcement_with_guest_account()
    {
        $res = $this->json('DELETE', $this->prefix . '/1', []);
        $code = $res->response->getStatusCode();
        $content = $res->response->getContent();
        $this->assertEquals(401, $code, "Status code was different from 401");
        $this->assertEquals('Unauthorized.', $content, "Didn't reject because user was not authorized.");
    }

    public function test_handle_delete_announcement_with_weak_account()
    {
        $res = $this->json('DELETE', $this->prefix . '/1', [], $this->getUserHeader());
        $code = $res->response->getStatusCode();
        $content = $res->response->getContent();
        $this->assertEquals(401, $code, "Status code was different from 401");
        $this->assertEquals('Rank Unauthorized.', $content, "Didn't reject because of wrong Rank.");
    }

    public function test_handle_delete_announcement_that_doesnt_exist()
    {
        $res = $this->json('DELETE', $this->prefix . '/69', [], $this->getAdminHeader());
        $code = $res->response->getStatusCode();
        $this->assertEquals(404, $code, 'Status code was not expected. Expected 404; Found: ' . $code);
    }
}
