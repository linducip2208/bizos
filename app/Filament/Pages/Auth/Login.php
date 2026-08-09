<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected string $view = 'auth.login';

    protected static string $layout = 'auth.login-layout';

    public function mount(): void
    {
        if (filament()->auth()->check()) {
            redirect()->intended(filament()->getUrl());
        }

        parent::mount();
    }
}
