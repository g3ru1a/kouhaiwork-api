<?php

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    private $version;

    public function setUp(): void
    {
        parent::setUp();
        $this->version = env('APP_VERSION', 'nan');
        // seed the database
        $this->artisan('db:seed');
        // alternatively you can call
        // $this->seed();
    }

    public function test_can_login(){
        $formData = [
            'email' => 'test@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', '/v'.$this->version.'/auth/login', $formData);
        $this->assertTrue(isset($res->response->original['data']['access_token']));
    }

    public function test_handled_login_with_wrong_email()
    {
        $formData = [
            'email' => 'test2@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/login', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "404");
    }

    public function test_handled_login_with_wrong_password()
    {
        $formData = [
            'email' => 'test@email.com',
            'password' => '1234567'
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/login', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "403");
    }

    public function test_can_register(){
        $formData = [
            'name' => 'whacky-acc',
            'email' => 'whacky@email.com',
            'password' => 'ComplexPassword!1',
            'password_confirmation' => 'ComplexPassword!1',
            'skip_email' => true,
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/register', $formData);
        $this->assertTrue(
            isset($res->response->original['data']['user']) &&
            $res->response->original['data']['user']->id === 4);
    }

    public function test_handle_register_with_wrong_data()
    {
        $formData = [
            'name' => 'testacc-2',
            'email' => 'test2',
            'password' => '123456',
            'password_confirmation' => '1234',
            'skip_email' => true,
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/register', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "422");
    }
    
    public function test_can_verify_email(){
        $user = User::factory()->create();
        $res = $this->json('POST', '/v' . $this->version . '/auth/verify/'. $user->id.'/'. $user->verify_token);
        $this->assertTrue(
            isset($res->response->original['data']) &&
            $res->response->original['data']['message'] === "Account Verified.");
    }

    public function test_can_request_password_reset()
    {
        $user = User::factory()->create();
        $user->verified = 1;
        $user->verify_token = '';
        $user->save();
        $formData = [
            'email' => $user->email,
            'skip_email' => true,
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/request/', $formData);
        $u = User::find($user->id);
        $this->assertTrue(
            isset($res->response->original['data']) &&
            $res->response->original['data']['message'] === "Password Reset Requested." &&
            $u->verify_token != ''
        );
    }

    public function test_handle_request_password_reset_with_wrong_data()
    {
        $formData = [
            'email' => 'asdasd asd',
            'skip_email' => true,
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/request/', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "422"
        );
    }
    public function test_handle_request_password_reset_with_unverified_email()
    {
        $user = User::factory()->create();
        $formData = [
            'email' => $user->email,
            'skip_email' => true,
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/request/', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "403"
        );
    }

    public function test_can_reset_password()
    {
        $user = User::factory()->create();
        $user->verified = 1;
        $user->verify_token = '';
        $user->save();
        $formData = [
            'email' => $user->email,
            'skip_email' => true,
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/request/', $formData);
        $u = User::find($user->id);
        $formData = [
            'user_id' => $u->id,
            'token' => $u->verify_token,
            'password' => 'NewPasswordEpic.1!',
            'password_confirmation' => 'NewPasswordEpic.1!',
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/reset/', $formData);
        $u = User::find($user->id);
        $this->assertTrue(
            isset($res->response->original['data']) &&
            $res->response->original['data']['message'] === "Password Reset." &&
            $u->verify_token == ''
        );
    }

    public function test_handle_reset_password_with_wrong_user_id(){
        $formData = [
            'user_id' => '69',
            'token' => 'thisisnotagoodtoken',
            'password' => 'NewPasswordEpic.1!',
            'password_confirmation' => 'NewPasswordEpic.1!',
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/reset/', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "404"
        );
    }

    public function test_handle_reset_password_with_wrong_token()
    {
        $formData = [
            'user_id' => '1',
            'token' => 'thisisnotagoodtoken',
            'password' => 'NewPasswordEpic.1!',
            'password_confirmation' => 'NewPasswordEpic.1!',
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/reset/', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
            $res->response->original['error']['status'] === "403"
        );
    }

    public function test_handle_reset_password_with_wrong_data()
    {
        $formData = [
            'user_id' => 'ab',
            'password' => 'NewPasswordEpic.1!',
            'password_confirmation' => 'NewPasswor22dEpic.1!',
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/password/reset/', $formData);
        $this->assertTrue(
            isset($res->response->original['error']) &&
                $res->response->original['error']['status'] === "422"
        );
    }

    public function test_auth_check_logged_in(){
        $formData = [
            'email' => 'test@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', '/v' . $this->version . '/auth/login', $formData);
        $token = $res->response->original['data']['access_token'];
        $res = $this->json('POST', '/v' . $this->version . '/auth/check/', [], [
            'Authorization' => 'Bearer '.$token
        ]);
        $this->assertTrue(
            isset($res->response->original['data']) &&
            $res->response->original['data']['message'] === "Logged In"
        );
    }

    public function test_auth_check_not_logged_in()
    {
        $res = $this->json('POST', '/v' . $this->version . '/auth/check/');
        $this->assertTrue($res->response->original == "Unauthorized.");
    }
}
