<?php
return [
    'min_length'       => env('PASSWORD_MIN_LENGTH', 12),
    'expiry_days'      => env('PASSWORD_EXPIRY_DAYS', 30),
    'history_count'    => env('PASSWORD_HISTORY_COUNT', 2),  // prevent reuse of last N passwords
    'require_upper'    => true,
    'require_lower'    => true,
    'require_number'   => true,
    'require_special'  => true,
];
