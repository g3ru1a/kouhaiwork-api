<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class GroupTest extends TestCase
{
    use DatabaseMigrations;

    private $version, $prefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = '/v' . env('APP_VERSION', 'nan');
        $this->prefix = '/v' . env('APP_VERSION', 'nan') . '/groups/me';
        // seed the database
        $this->artisan('db:seed');
    }

    public function test_can_get_all_users_groups(){
        $res = $this->json('GET', $this->prefix.'/', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data) && count($res->data) == 6);
    }

    public function test_can_get_groups_where_the_user_is_any()
    {
        $res = $this->json('GET', $this->prefix . '/where/any', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data) && count($res->data) == 6);
    }

    public function test_can_get_groups_where_the_user_is_owner()
    {
        $res = $this->json('GET', $this->prefix . '/where/owner', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data) && count($res->data) == 5, 'Expected 5, Found:'.count($res->data));
    }

    public function test_can_get_groups_where_the_user_is_member()
    {
        $res = $this->json('GET', $this->prefix . '/where/member', [], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data) && count($res->data) == 1);
    }

    public function test_can_create_group()
    {
        $res = $this->json('POST', $this->prefix . '/', [
            'name' => 'testgroup yuh'
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data->id));
    }
    public function test_can_update_group()
    {
        $res = $this->json('PUT', $this->prefix . '/1', [
            'name' => 'testgroup'
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->data->id));
    }
    public function test_handle_create_or_update_group_with_taken_name()
    {
        $res = $this->json('POST', $this->prefix . '/', [
            'name' => 'GroupSeed'
        ], $this->getAdminHeader());
        $res = $res->response->getData();
        $this->assertTrue(isset($res->error->status) && $res->error->status == '422');
    }

    public function test_handle_create_or_update_group_with_user_account()
    {
        $res = $this->json('POST', $this->prefix . '/', [
            'name' => 'GroupSeed'
        ], $this->getUserHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 401);
    }

    public function test_handle_create_or_update_group_as_guest()
    {
        $res = $this->json('POST', $this->prefix . '/', [
            'name' => 'GroupSeed'
        ]);
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 401);
    }

    public function test_can_delete_group()
    {
        $res = $this->json('DELETE', $this->prefix . '/5', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 200);
    }

    public function test_handle_delete_group_with_user_account()
    {
        $res = $this->json('DELETE', $this->prefix . '/5', [], $this->getUserHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 401);
    }

    public function test_handle_delete_group_as_guest()
    {
        $res = $this->json('DELETE', $this->prefix . '/5', []);
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 401);
    }

    public function test_handle_delete_group_where_user_is_not_owner()
    {
        $res = $this->json('DELETE', $this->prefix . '/2', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 406);
    }

    public function test_handle_delete_group_that_doesnt_exist()
    {
        $res = $this->json('DELETE', $this->prefix . '/69', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 404);
    }
    
    public function test_can_get_group_members(){
        $res = $this->json('GET', $this->prefix . '/1/members', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 200);
    }

    public function test_handle_get_group_members_where_user_is_not_owner()
    {
        $res = $this->json('GET', $this->prefix . '/2/members', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 406);
    }

    public function test_handle_get_group_members_where_group_doesnt_exist()
    {
        $res = $this->json('GET', $this->prefix . '/69/members', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 404);
    }

    public function test_can_add_group_members()
    {
        $res = $this->json('POST', $this->prefix . '/5/members', [
            'users' => json_encode(['2'])
        ], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 200);
    }

    public function test_handle_add_group_members_where_user_is_not_owner()
    {
        $res = $this->json('POST', $this->prefix . '/2/members', [
            'users' => json_encode(['2'])
        ], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 406);
    }

    public function test_handle_add_group_members_where_group_doesnt_exist()
    {
        $res = $this->json('POST', $this->prefix . '/69/members', [
            'users' => json_encode(['2'])
        ], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 404);
    }

    public function test_can_kick_group_members()
    {
        $res = $this->json('DELETE', $this->prefix . '/1/members', [
            'members' => json_encode(['2'])
        ], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 200);
    }

    public function test_handle_kick_group_members_where_user_is_not_owner()
    {
        $res = $this->json('DELETE', $this->prefix . '/2/members', [
            'members' => json_encode(['2'])
        ], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 406);
    }

    public function test_handle_delete_group_members_where_group_doesnt_exist()
    {
        $res = $this->json('DELETE', $this->prefix . '/69/members', [
            'members' => json_encode(['2'])
        ], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 404);
    }

    public function test_can_leave_group()
    {
        $res = $this->json('POST', $this->prefix . '/2/leave', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 200);
    }

    public function test_handle_leave_group_that_user_owns()
    {
        $res = $this->json('POST', $this->prefix . '/5/leave', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 406);
    }

    public function test_handle_leave_group_that_user_is_not_part_of()
    {
        $res = $this->json('POST', $this->prefix . '/3/leave', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 406);
    }

    public function test_handle_leave_group_that_doesnt_exist()
    {
        $res = $this->json('POST', $this->prefix . '/69/leave', [], $this->getAdminHeader());
        $statusCode = $res->response->getStatusCode();
        $this->assertTrue(isset($statusCode) && $statusCode == 404);
    }
}
