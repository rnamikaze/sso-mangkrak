<?php

namespace App\Http\Controllers\SKU;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChangelogControllers extends Controller
{
    //
    public function index()
    {
        return Inertia::render('SKU/Changelog/Index');
    }
}
