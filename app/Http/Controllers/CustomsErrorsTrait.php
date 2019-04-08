<?php

namespace App\Http\Controllers;

trait CustomsErrorsTrait
{

	public function getErrorMessage($message)
    {
        return
        [
            [
                'status' => 'FAILED',
                'message' => $message,
            ]
        ];
    }
}