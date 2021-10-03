<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class DemographicTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefix = '/v' . env('APP_VERSION', 'nan') . '/groups/manga/demographics';
        // seed the database
        $this->artisan('db:seed');
    }

    private function getAdminHeader(){
        $formData = [
            'email' => 'test@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', $this->version . '/auth/login', $formData);
        $token = $res->response->original['data']['access_token'];
        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }

    private function getUserHeader()
    {
        $formData = [
            'email' => 'test-weak@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', $this->version . '/auth/login', $formData);
        $token = $res->response->original['data']['access_token'];
        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }

    public function test_can_fetch_demographics()
    {
        $res = $this->json('GET', $this->version . '/manga/demographics');
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_can_add_demographic(){
        $res = $this->json('POST', $this->prefix, [
            'name' => 'testo namewa',
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_handle_add_demographic_request_from_guest_user()
    {
        $res = $this->json('POST', $this->prefix, [
            'name' => 'testo namewa',
        ]);
        $res = $res->response->original;
        $this->assertTrue($res === 'Unauthorized.');
    }
    public function test_handle_add_demographic_request_from_normal_user()
    {
        $res = $this->json('POST', $this->prefix, [
            'name' => 'testo namewa',
        ], $this->getUserHeader());
        $res = $res->response->original;
        $this->assertTrue($res === 'Rank Unauthorized.');
    }

    public function test_can_update_demographics()
    {
        $res = $this->json('PUT', $this->prefix . '/3', [
            'name' => 'testo namewa',
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_handle_update_demographic_request_with_wrong_id()
    {
        $res = $this->json('PUT', $this->prefix . '/69', [
            'name' => 'testo namewa',
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->error) && $res->error->status === '404');
    }

    public function test_can_delete_demographics()
    {
        $res = $this->json('DELETE', $this->prefix . '/3', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data));
    }

    public function test_handle_delete_demographic_request_with_wrong_id()
    {
        $res = $this->json('DELETE', $this->prefix . '/69', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->error) && $res->error->status === '404');
    }
}
