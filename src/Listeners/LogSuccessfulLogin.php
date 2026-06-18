<?php

namespace ImranSaleem\SecuritySuite\Listeners;

use Illuminate\Auth\Events\Login;
use ImranSaleem\SecuritySuite\Services\LoginBlockService;
use ImranSaleem\SecuritySuite\Services\LoginLogService;

class LogSuccessfulLogin
{
    protected LoginLogService $logger;
    protected LoginBlockService $blocker;

    public function __construct(LoginLogService $logger, LoginBlockService $blocker)
    {
        $this->logger  = $logger;
        $this->blocker = $blocker;
    }

    public function handle(Login $event): void
    {
        $this->blocker->resetOnSuccess($event->user);
        $this->logger->logSuccess($event->user);
    }
}
