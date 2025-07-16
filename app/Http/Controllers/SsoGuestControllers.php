<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SsoGuestControllers extends Controller
{
    //
    public function privacyAndPolicy()
    {
        return Inertia::render('Guest/PrivacyPolicyPage', []);
    }
}
