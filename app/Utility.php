<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Utility
{
    public static function log($screen, $error_msg)
    {
        Log::error("\n\n" . $screen . " - \n" . $error_msg);
    }
}
