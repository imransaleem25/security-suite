<?php

namespace ImranSaleem\SecuritySuite\Http\Controllers;

use Illuminate\Routing\Controller;
use ImranSaleem\SecuritySuite\Services\IdleTimeoutService;

class IdleController extends Controller
{
    protected IdleTimeoutService $idle;

    public function __construct(IdleTimeoutService $idle)
    {
        $this->idle = $idle;
    }

    public function ping()
    {
        $this->idle->touch();
        return response()->json(['ok' => true]);
    }

    public function config()
    {
        return response()->json([
            'timeout_ms'  => $this->idle->getTimeoutMs(),
            'warn_before' => $this->idle->getWarnBeforeMs(),
            'logout_url'  => route('logout'),
        ]);
    }
}
