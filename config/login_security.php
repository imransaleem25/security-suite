<?php
return [
    'max_attempts'    => env('LOGIN_MAX_ATTEMPTS', 5),
    'block_mode'      => env('LOGIN_BLOCK_MODE', 'temporary'), // 'temporary' or 'admin'
    'lockout_minutes' => env('LOGIN_LOCKOUT_MINUTES', 15),
];
