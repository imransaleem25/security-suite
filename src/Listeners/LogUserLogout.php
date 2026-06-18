<?php

namespace ImranSaleem\SecuritySuite\Listeners;

use Illuminate\Auth\Events\Logout;
use ImranSaleem\SecuritySuite\Services\LoginLogService;

class LogUserLogout
{
    protected LoginLogService $logger;

    public function __construct(LoginLogService $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Logout $event): void
    {
        $this->logger->logLogout($event->user);
    }
}
