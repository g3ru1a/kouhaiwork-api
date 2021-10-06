<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function getAdminHeader()
    {
        $version = '/v' . env('APP_VERSION', 'nan');
        $formData = [
            'email' => 'test@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', $version . '/auth/login', $formData);
        $token = $res->response->original['data']['access_token'];
        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }

    public function getUserHeader()
    {
        $version = '/v' . env('APP_VERSION', 'nan');
        $formData = [
            'email' => 'test-weak@email.com',
            'password' => '123456'
        ];
        $res = $this->json('POST', $version . '/auth/login', $formData);
        $token = $res->response->original['data']['access_token'];
        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }
}
