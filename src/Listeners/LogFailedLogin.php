<?php

namespace ImranSaleem\SecuritySuite\Listeners;

use Illuminate\Auth\Events\Failed;
use ImranSaleem\SecuritySuite\Services\LoginBlockService;
use ImranSaleem\SecuritySuite\Services\LoginLogService;

class LogFailedLogin
{
    protected LoginLogService $logger;
    protected LoginBlockService $blocker;

    public function __construct(LoginLogService $logger, LoginBlockService $blocker)
    {
        $this->logger  = $logger;
        $this->blocker = $blocker;
    }

    public function handle(Failed $event): void
    {
        $email = $event->credentials['email']
            ?? $event->credentials['username']
            ?? null;

        $reason = 'Invalid credentials or unknown user.';

        if ($event->user) {
            if (config('login_security.auto_block_via_events', true)) {
                $this->blocker->recordFailure($event->user);
            }
            $blockMessage = $this->blocker->checkBlock($event->user);
            if ($blockMessage) {
                $reason = $blockMessage;
            }
            $this->logger->logFailure($event->user, is_string($email) ? $email : null, $reason);
            return;
        }

        $this->logger->logFailure(null, is_string($email) ? $email : null, $reason);
    }
}
