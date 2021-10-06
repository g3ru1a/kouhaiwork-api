<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    protected $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function render($request)
    {
        return response()->json(['error' => [
            'status' => '422',
            'message' => 'Invalid Parameters: ' . $this->message
        ]], 422);
    }
}