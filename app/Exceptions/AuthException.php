<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    protected $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function render($request)
    {
        return response()->json(['error' => [
            'status' => '403',
            'message' => $this->message
        ]], 403);
    }
}