<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        /** @var User|null $user */
        $user = $event->user;
        if ($user) {
            AuditLog::logActivity(
                description: "User \"{$user->name}\" ({$user->email}) logged into the system",
                auditable: $user,
                event: AuditLog::EVENT_LOGIN,
                oldValue: null,
                newValue: [
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                properties: [
                    'guard' => $event->guard,
                ],
                user: $user,
                action: 'login'
            );
        }
    }

    public function handleLogout(Logout $event): void
    {
        /** @var User|null $user */
        $user = $event->user;
        if ($user) {
            AuditLog::logActivity(
                description: "User \"{$user->name}\" ({$user->email}) signed out",
                auditable: $user,
                event: AuditLog::EVENT_LOGOUT,
                oldValue: null,
                newValue: null,
                properties: [
                    'guard' => $event->guard,
                ],
                user: $user,
                action: 'logout'
            );
        }
    }
}
