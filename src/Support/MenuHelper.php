<?php

namespace ImranSaleem\SecuritySuite\Support;

class MenuHelper
{
    public static function canViewLogsMenu(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        if (!(config('security_suite.menu.enabled', true))) {
            return false;
        }

        $viewerRole = config('security_suite.menu.viewer_role', config('audit.viewer_role', 'admin'));
        $user       = auth()->user();

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($viewerRole);
        }

        return true;
    }
}
