<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DebugControllers extends Controller
{
    //
    public function newDashboard()
    {
        return Inertia::render('SimpegUnusida/SIKMain');
    }

    public function imageCompression()
    {
        return Inertia::render('Debug/ImageCompressionSideBySide');
    }
}
